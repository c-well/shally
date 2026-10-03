# Adventure artwork

Generated with the built-in image-generation tool on October 3, 2026. The web JPEG is resized to 1200 pixels wide for loading performance. Source PNG is retained in the task's generated image directory.

Prompt: Create a polished wide landscape 3:2 illustration for a modern children's Bible adventure game website, no text, no letters, no logos, no interface. A delightful stylized 3D clay diorama: small friendly fluffy cream sheep explorer with a coral backpack standing on a winding lilac path, a beautiful open book with luminous warm golden pages, chunky turquoise grassy floating islands, round soft trees, lavender hills, tiny golden four-point collectible stars floating above path, pale mint and periwinkle sky. Sophisticated contemporary family game art, Nintendo-like playful dimensional handcrafted clay material, very clean shapes, charming warm expression, premium studio lighting, soft shadows, vibrant coral lavender mint and sunshine yellow color palette, not emojis, not stock icons, not religious people. Main sheep on right half, book near center left. Fill composition with dreamy small adventure world, generous light sky at upper edge, no frame.

## Animated sheep layers

The built-in image tool produced two edits of the original artwork. The web animates the transparent sheep independently using CSS, with a tap-triggered hop and discovery celebrations. Motion may be paused and honors the operating system's reduced-motion setting.

Sheep prompt: Extract and faithfully recreate ONLY the friendly fluffy sheep explorer character from the reference image, preserving its cream wool, large shiny dark eyes, small tan face, tiny hooves, and coral backpack. Full body standing, adorable three-quarter view facing slightly left toward the viewer, eyes looking at viewer, warm smile. Exactly one sheep centered, feet near bottom, generous 8 percent transparent padding on all sides. Transparent background alpha PNG, no ground, no scenery, no stars, no book, no text, no surrounding objects or cast ground shadow. Polished dimensional clay/plush game mascot, same character and materials. This will be a standalone animated web mascot asset.

Background prompt: Edit this landscape game illustration. Remove the sheep explorer completely, including its backpack and shadow, and seamlessly continue the lilac stepping-stone path and soft turquoise grass behind where it stood. Keep everything else: same composition, floating islands, luminous open book, castle, trees, stars, color palette, camera perspective and dreamy 3D clay style. No animals or people anywhere. The right foreground must be an empty path platform where a separately animated mascot will be placed by the website. Do not add text.

Deploy `sheep-v1.png` and `adventure-path-v1.jpg` from this directory to the matching public/kids-assets directory. The original scene remains used for small static backdrop panels.

## 3D implementation

An experimental, currently disabled mascot has a code-built articulated mesh in `sheep-3d-v1.js`. Its wool, face, eyes, limbs, and coral backpack are lit and rendered with locally hosted Three.js 0.180.0. Dependency provenance: npm package `three@0.180.0`, MIT license retained in `vendor/three-0.180.0/LICENSE`. The previous generated PNG is the no-WebGL/context-loss fallback. The 3D model is procedural project code; no new image-generation prompt was used for this revision.

The friendly illustrated sheep is the active mascot. The procedural mesh was disabled after visual feedback; its assets remain available for future art refinement.
