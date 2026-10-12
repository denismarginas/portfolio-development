"""Project thumbnail generator.

Composes 3 screenshots into the tilted screen masks of
img/thumbnails/project_thumbnail and puts the overlay frame on top.

Usage:
  python generate_thumbnails.py <img_root> <project|ALL> [--variant web|media|default] [--dry-run] [--xl FILE --md FILE --sm FILE] [--out FILE]

  <img_root> = src/content/img   (needs Pillow: pip install pillow)
  ALL        = every project folder whose thumbnail/ has no image yet (agromir etc. are skipped)
"""
import argparse, glob, os
from PIL import Image, ImageChops

SIZE = 400
PAD = 300  # working canvas padding so rotated screens can extend past the edge


def load_masks(tpl_dir):
    masks = {}
    for key in ("xl", "md", "sm"):
        m = Image.open(os.path.join(tpl_dir, f"thumbnail_screen_1_{key}.webp")).convert("RGBA").getchannel("A")
        masks[key] = m
    overlay = Image.open(os.path.join(tpl_dir, "thumbnail_project_overlay.webp")).convert("RGBA")
    return masks, overlay


def mask_geometry(mask):
    """Find tilt angle and the screen rectangle (in the un-tilted frame)."""
    big = Image.new("L", (SIZE + 2 * PAD, SIZE + 2 * PAD), 0)
    big.paste(mask, (PAD, PAD))
    best = None
    for tenth in range(-150, 151):
        ang = tenth / 10
        r = big.rotate(ang, resample=Image.BILINEAR)
        bb = r.point(lambda v: 255 if v > 127 else 0).getbbox()
        area = (bb[2] - bb[0]) * (bb[3] - bb[1])
        if best is None or area < best[0]:
            best = (area, ang, bb)
    _, ang, bb = best
    return ang, bb


def cover(img, w, h, anchor_x=0.5, anchor_y=0.0):
    img = img.convert("RGBA")
    s = max(w / img.width, h / img.height)
    nw, nh = max(w, round(img.width * s)), max(h, round(img.height * s))
    img = img.resize((nw, nh), Image.LANCZOS)
    x = round((nw - w) * anchor_x)
    y = round((nh - h) * anchor_y)
    return img.crop((x, y, x + w, y + h))


# how each screen is cropped from its source (x anchor: 0 left, .5 center, 1 right)
ANCHORS = {"xl": (0.0, 0.0), "md": (0.5, 0.0), "sm": (0.5, 0.0)}


def compose(sources, masks, overlay, geo):
    out = Image.new("RGBA", (SIZE, SIZE), (0, 0, 0, 0))
    for key in ("xl", "md", "sm"):  # back to front
        src = sources.get(key)
        if not src:
            continue
        ang, (x0, y0, x1, y1) = geo[key]
        ax, ay = ANCHORS[key]
        tile = cover(Image.open(src), x1 - x0, y1 - y0, ax, ay)
        layer = Image.new("RGBA", (SIZE + 2 * PAD, SIZE + 2 * PAD), (0, 0, 0, 0))
        layer.paste(tile, (x0, y0))
        layer = layer.rotate(-ang, resample=Image.BICUBIC)
        layer = layer.crop((PAD, PAD, PAD + SIZE, PAD + SIZE))
        alpha = ImageChops.multiply(layer.getchannel("A"), masks[key])
        layer.putalpha(alpha)
        out.alpha_composite(layer)
    out.alpha_composite(overlay)
    return out


IMG_EXT = (".webp", ".png", ".jpg", ".jpeg")


def files(pattern):
    return sorted(f for f in glob.glob(pattern, recursive=True) if f.lower().endswith(IMG_EXT))


def pick_sources(project_dir, variant):
    overview = files(os.path.join(project_dir, "web", "overview", "*desktop*"))
    desktop = files(os.path.join(project_dir, "web", "desktop", "*"))
    # the first desktop screenshot is usually the same homepage as the overview, so prefer the 2nd one for xl
    web_desktop = overview + (desktop[1:] + desktop[:1] if overview and len(desktop) > 1 else desktop)
    web_phone = files(os.path.join(project_dir, "web", "overview", "*phone*")) + files(os.path.join(project_dir, "web", "phone", "*"))
    media = files(os.path.join(project_dir, "media", "**", "*"))
    if variant == "default":
        variant = "web" if web_desktop else "media"
    if variant == "web":
        d = web_desktop
        return {"md": d[0] if d else None,
                "xl": d[1] if len(d) > 1 else (d[0] if d else None),
                "sm": web_phone[0] if web_phone else (d[2] if len(d) > 2 else None)}
    # media: spread picks across the list for variety
    if not media:
        return {}
    n = len(media)
    return {"md": media[0], "sm": media[n // 2], "xl": media[min(n - 1, (2 * n) // 3 + 1)]}


def has_thumbnail(project_dir):
    return bool(files(os.path.join(project_dir, "thumbnail", "*")))


def main():
    p = argparse.ArgumentParser()
    p.add_argument("img_root")
    p.add_argument("project", help="project folder name, or ALL = every project whose thumbnail folder has no image yet")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--variant", default="default", choices=["default", "web", "media"])
    p.add_argument("--xl"); p.add_argument("--md"); p.add_argument("--sm")
    p.add_argument("--out")
    a = p.parse_args()
    masks, overlay = load_masks(os.path.join(a.img_root, "thumbnails", "project_thumbnail"))
    geo = {k: mask_geometry(m) for k, m in masks.items()}
    if a.project == "ALL":
        root = os.path.join(a.img_root, "projects")
        todo = [d for d in sorted(os.listdir(root)) if os.path.isdir(os.path.join(root, d)) and not has_thumbnail(os.path.join(root, d))]
    else:
        todo = [a.project]
    for name in todo:
        run(a, name, masks, overlay, geo)


def run(a, project, masks, overlay, geo):
    pdir = os.path.join(a.img_root, "projects", project)
    src = pick_sources(pdir, a.variant)
    for k in ("xl", "md", "sm"):
        if getattr(a, k):
            src[k] = getattr(a, k)
    if not any(src.values()):
        print(f"skip {project}: no images"); return
    suffix = "" if a.variant == "default" else "_" + a.variant
    out = a.out or os.path.join(pdir, "thumbnail", f"thumbnail_{project}{suffix}.webp")
    if a.dry_run:
        print("would save", out, {k: os.path.basename(v) for k, v in src.items() if v}); return
    img = compose(src, masks, overlay, geo)
    os.makedirs(os.path.dirname(out), exist_ok=True)
    img.save(out, "WEBP", quality=85, method=6)
    print("saved", out, {k: os.path.basename(v) for k, v in src.items() if v})


if __name__ == "__main__":
    main()
