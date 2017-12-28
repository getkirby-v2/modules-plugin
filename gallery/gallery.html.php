<div class="gallery">
	<?php foreach($module->images()->sortBy('sort', 'asc') as $image): ?>
		<!-- The alt attribute will use the page title if the image does not have one -->
		<img src="<?= $image->url() ?>" alt="<?= $image->alt()->or($page->title()) ?>">
	<?php endforeach ?>
</div>
