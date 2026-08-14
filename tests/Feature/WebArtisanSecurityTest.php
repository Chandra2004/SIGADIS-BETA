<?php

it('does not expose the webartisan console route by default', function () {
    $this->get('/webartisan')->assertNotFound();
});
