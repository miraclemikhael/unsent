<?php
defined('ROOT') || exit;
http_response_code(404);
head('Not found');
shell('');
top('Page not found', 'That page isn’t here.', '<a class="btn" href="?p=home">' . ic('home', 17) . 'Back home</a>');
