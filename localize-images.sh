#!/usr/bin/env bash
# Downloads the nine landing page photographs into public/images/landing/
# and prints the $img array to paste into resources/views/landing.blade.php.
#
# Run from the repo root:   bash localize-images.sh
#
# Why bother: the page currently loads these straight from i.pinimg.com.
# Pinterest blocks hotlinking without warning, and when it does the photographs
# vanish from a live site. Serving them yourself also drops a third-party
# request from every page load.

set -uo pipefail

DEST="public/images/landing"

if [ ! -f "artisan" ]; then
  echo "Run this from the repo root (the folder containing 'artisan')." >&2
  exit 1
fi

mkdir -p "$DEST"

# slot=url   — slot names match the $img keys in landing.blade.php
  $img = [
    'hero1'  => '/images/landing/hero1.jpg',
    'hero2'  => '/images/landing/hero2.jpg',
    'phone'  => '/images/landing/phone.jpg',
    'band1'  => '/images/landing/band1.jpg',
    'cost'   => '/images/landing/cost.jpg',
    'band2'  => '/images/landing/band2.jpg',
    'detail' => '/images/landing/detail.jpg',
    'cta'    => '/images/landing/cta.jpg',
    'clip'   => '/images/landing/clip.jpeg',
  ];

FAILED=()

for entry in "${IMAGES[@]}"; do
  slot="${entry%%=*}"
  url="${entry#*=}"
  out="$DEST/$slot.jpg"

  printf '%-8s ' "$slot"

  if curl -fsSL --retry 2 --max-time 45 \
       -H 'User-Agent: Mozilla/5.0' \
       -H 'Referer: https://www.pinterest.com/' \
       -o "$out" "$url"; then
    # Reject an HTML error page that arrived with a 200
    if file --mime-type -b "$out" 2>/dev/null | grep -q '^image/'; then
      printf 'ok    %-36s %s\n' "$out" "$(du -h "$out" | cut -f1)"
    else
      printf 'BAD   response was not an image, removed\n'
      rm -f "$out"
      FAILED+=("$slot|$url")
    fi
  else
    printf 'FAIL  could not fetch\n'
    FAILED+=("$slot|$url")
  fi
done

echo
if [ "${#FAILED[@]}" -gt 0 ]; then
  echo "${#FAILED[@]} image(s) failed. Save each by hand into $DEST/"
  echo "using the slot name as the filename:"
  echo
  for f in "${FAILED[@]}"; do
    printf '  %-8s -> %s/%s.jpg\n' "${f%%|*}" "$DEST" "${f%%|*}"
    printf '           %s\n' "${f#*|}"
  done
  echo
fi

cat <<'ARRAY'
Replace the $img array near the top of resources/views/landing.blade.php with:

  $img = [
    'hero1'  => '/images/landing/hero1.jpg',
    'hero2'  => '/images/landing/hero2.jpg',
    'phone'  => '/images/landing/phone.jpg',
    'band1'  => '/images/landing/band1.jpg',
    'cost'   => '/images/landing/cost.jpg',
    'band2'  => '/images/landing/band2.jpg',
    'detail' => '/images/landing/detail.jpg',
    'cta'    => '/images/landing/cta.jpg',
    'clip'   => '/images/landing/clip.jpeg',
  ];

Where each one appears on the page:

  hero1, hero2  hero background, crossfading every 9 seconds
  band1         full bleed band under the stats
  clip          small square clipped to the counter book corner
  phone         the handset, with the SMS screen drawn over it
  cost          behind the "Compared to what?" section, at 16%
  detail        3:4 framed image beside the small things list
  band2         full bleed band before pricing
  cta           behind the closing call to action
ARRAY