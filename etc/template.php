<?php

namespace Kirby\Modules;

// Redirect to the page where the module appears
if($page->parent()->uid() === Modules::parentUid()) {
	go($page->parent()->parent());
} else {
	go($page->parent());
}
