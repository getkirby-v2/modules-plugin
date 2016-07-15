<?php

namespace Kirby\Modules;

// Redirect to the page where the module appears
if($page->parent()->uid() === Modules::uid()) {
	go($page->parent()->parent());
} else {
	go($page->parent());
}
