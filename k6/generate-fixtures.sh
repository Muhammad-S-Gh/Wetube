#!/usr/bin/env sh
set -eu

# Generates the small, deterministic files used by both k6 scenarios.
# Existing checked-in fixtures mean this is only needed when regenerating them.
ffmpeg -y -f lavfi -i testsrc=size=640x360:rate=24 -f lavfi -i sine=frequency=1000 \
    -t 2 -c:v libx264 -pix_fmt yuv420p -c:a aac -shortest k6/sample_video.mp4
ffmpeg -y -f lavfi -i color=c=steelblue:s=640x360 -frames:v 1 k6/sample_thumb.jpg
