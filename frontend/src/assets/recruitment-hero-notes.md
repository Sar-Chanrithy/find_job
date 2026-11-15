# Recruitment banner asset

Generated with the built-in image_gen tool. Final project asset: `recruitment-hero-cutout.png` (PNG with alpha; corner alpha verified as 0). The original generated version is retained as `recruitment-hero.png`.

Initial prompt:

> Create one premium photorealistic website hero cutout asset, transparent background. Subject: smiling adult Southeast Asian female professional with long dark softly waved hair, wearing a beautifully tailored rich plum-purple blazer over an ivory blouse, looking down at a silver tablet held naturally in both hands. Three-quarter body from head to upper thighs, entire hair and arms within image edges, straight upright pose slightly turned to viewer left. Professional studio lighting with gentle warm skin tones and subtle purple rim light. Editorial recruitment campaign photograph, elegant confident approachable, realistic hands. Portrait composition, subject fills frame with small transparent margin. ONLY the woman and tablet, no text, no icons, no shapes, no background, no watermark. Intended to overlay a dark navy recruitment banner similar to a purple blazer professional holding tablet.

Final refinement prompt:

> Remove the entire background and purple glow from this image. Keep only the exact woman, clothing and tablet unchanged, with true transparent alpha around her silhouette. No black background, no studio background, no gradient, no glow, no checkerboard drawn in image. Clean isolated cutout for compositing over a navy web hero.

The surrounding banner is responsive HTML/CSS, with live search and API-derived job count. The person is a generated illustration of a professional, not a testimonial or real employee endorsement.

## Job search carousel slide

Asset: `job-search-banner.png`, generated with the built-in image_gen tool as a new original illustration inspired by the user's line-art reference. It is not the supplied VectorStock image.

Prompt:

> Original widescreen job recruitment website banner, 1536x1024 landscape. Premium clean flat vector-style illustration, deep slate blue #344779 background, fine mint cyan, white and warm amber line art icons arranged in a decorative border around central large bold words 'JOB SEARCH' in bright pale mint. Icons: CV document, magnifying glass, briefcase, email envelope, applicant profile, globe, laptop, conversation bubbles, lightbulb. Elegant dotted connecting paths between the icons. Lots of clean space, crisp geometric lines, balanced professional design, modern and optimistic. Typography must read exactly JOB SEARCH, only those two words, no logos, no signatures, no watermark. This is a new original illustration inspired by the general concept of job search line art, not a copy of any stock asset.

Carousel behavior: two slides, 5.5-second interval, CSS slide-and-fade transition, previous/next controls, indicator dots, pause/play, and touch swipe. Rotation pauses on hover, keyboard focus, or a hidden browser tab; reduced-motion preferences disable auto-rotation and transitions. Manual navigation pauses auto-rotation. The search form remains mounted and does not lose its input during slide changes.
