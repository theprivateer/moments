#!/usr/bin/env python3
"""
Instagram → Moments importer
Reads posts_1.json and uploads historic Instagram posts to the Moments API.

Configuration (environment variables):
    MOMENTS_API_BASE   Base URL of the Moments site, e.g. https://moments.example.com
    MOMENTS_TOKEN      Sanctum personal access token, created at /account

Usage:
    export MOMENTS_API_BASE="https://moments.example.com"
    export MOMENTS_TOKEN="..."

    python3 upload_to_moments.py            # upload all posts
    python3 upload_to_moments.py --limit 3  # upload first 3 posts (for testing)
    python3 upload_to_moments.py --resume   # skip posts already logged as uploaded

Requirements:
    pip install requests
"""

import json
import os
import sys
import time
import argparse
import requests

# ── Configuration ─────────────────────────────────────────────────────────────

API_BASE    = os.environ.get("MOMENTS_API_BASE", "").rstrip("/")
TOKEN       = os.environ.get("MOMENTS_TOKEN", "")
POSTS_FILE  = os.path.join(os.path.dirname(__file__), "posts_1.json")
MEDIA_ROOT  = os.path.dirname(__file__)          # images are relative to this dir
LOG_FILE    = os.path.join(os.path.dirname(__file__), "upload_log.json")

# ── Helpers ────────────────────────────────────────────────────────────────────

def fix_encoding(text: str) -> str:
    """Fix Instagram's mojibake: UTF-8 bytes stored as latin-1."""
    if not text:
        return text
    try:
        return text.encode("latin-1").decode("utf-8")
    except (UnicodeEncodeError, UnicodeDecodeError):
        return text


def upload_image(file_path: str, session: requests.Session) -> int:
    """Upload a single image file; returns the API image ID."""
    with open(file_path, "rb") as f:
        resp = session.post(
            f"{API_BASE}/api/v1/images",
            files={"image": (os.path.basename(file_path), f, "image/jpeg")},
        )
    resp.raise_for_status()
    return resp.json()["data"]["id"]


def create_moment(body: str, image_ids: list[int], created_at: int,
                  session: requests.Session) -> dict:
    """Create a moment and return the response data dict."""
    payload: dict = {"created_at": created_at, "images": image_ids}
    if body:
        payload["body"] = body
    resp = session.post(f"{API_BASE}/api/v1/moments", json=payload)
    resp.raise_for_status()
    return resp.json()["data"]


def load_log() -> dict:
    if os.path.exists(LOG_FILE):
        with open(LOG_FILE) as f:
            return json.load(f)
    return {"uploaded": []}


def save_log(log: dict) -> None:
    with open(LOG_FILE, "w") as f:
        json.dump(log, f, indent=2)


# ── Main ───────────────────────────────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(description="Upload Instagram posts to Moments API")
    parser.add_argument("--limit", type=int, default=None,
                        help="Only upload the first N eligible posts")
    parser.add_argument("--resume", action="store_true",
                        help="Skip posts already recorded in upload_log.json")
    args = parser.parse_args()

    missing = [name for name, value in (("MOMENTS_API_BASE", API_BASE),
                                        ("MOMENTS_TOKEN", TOKEN)) if not value]
    if missing:
        sys.exit(f"Missing required environment variable(s): {', '.join(missing)}. "
                 "See the module docstring for setup instructions.")

    with open(POSTS_FILE) as f:
        all_posts = json.load(f)

    log = load_log()
    already_uploaded = set(log["uploaded"]) if args.resume else set()

    session = requests.Session()
    session.headers.update({
        "Authorization": f"Bearer {TOKEN}",
        "Accept": "application/json",
    })

    # Build eligible post list (skip video-only posts)
    eligible = []
    for idx, item in enumerate(all_posts):
        media_list = item.get("media", [])
        images = [m for m in media_list if not m.get("uri", "").endswith(".mp4")]
        if not images:
            continue
        eligible.append((idx, item, images))

    total_eligible = len(eligible)
    if args.limit:
        eligible = eligible[: args.limit]

    print(f"Posts in file:     {len(all_posts)}")
    print(f"Eligible (no vids):{total_eligible}")
    print(f"To upload now:     {len(eligible)}")
    if args.resume:
        print(f"Already uploaded:  {len(already_uploaded)}")
    print()

    uploaded_count = 0
    skipped_count  = 0
    error_count    = 0

    for idx, item, images in eligible:
        # Unique key for this post (use uri of first image as stable ID)
        post_key = images[0]["uri"]

        if post_key in already_uploaded:
            skipped_count += 1
            continue

        # Caption: top-level title (carousel) or first media title (single)
        raw_caption = item.get("title") or images[0].get("title") or ""
        caption = fix_encoding(raw_caption)
        created_at = (item.get("creation_timestamp")
                      or images[0].get("creation_timestamp"))

        print(f"[{uploaded_count + 1}/{len(eligible) - skipped_count}] "
              f"{caption[:60]!r}  ({len(images)} image{'s' if len(images) != 1 else ''})")

        try:
            # 1. Upload images
            image_ids = []
            for m in images:
                file_path = os.path.join(MEDIA_ROOT, m["uri"])
                if not os.path.exists(file_path):
                    print(f"    WARNING: file not found, skipping — {m['uri']}")
                    continue
                img_id = upload_image(file_path, session)
                print(f"    ✓ {os.path.basename(m['uri'])} → image #{img_id}")

                image_ids.append(img_id)

            if not image_ids:
                print("    No images uploaded; skipping moment creation.\n")
                error_count += 1
                continue

            # 2. Create moment
            moment = create_moment(caption, image_ids, created_at, session)
            print(f"    ✓ Moment #{moment['id']} created — {moment['created_at']}\n")

            log["uploaded"].append(post_key)
            save_log(log)
            uploaded_count += 1

            # Be polite to the API
            time.sleep(0.25)

        except requests.HTTPError as e:
            print(f"    ✗ HTTP error: {e.response.status_code} — {e.response.text[:200]}\n")
            error_count += 1
        except Exception as e:
            print(f"    ✗ Unexpected error: {e}\n")
            error_count += 1

    print("─" * 60)
    print(f"Done.  Uploaded: {uploaded_count}  Skipped: {skipped_count}  Errors: {error_count}")
    if error_count:
        print("Re-run with --resume to retry failed posts.")


if __name__ == "__main__":
    main()
