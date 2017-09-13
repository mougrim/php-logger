#!/usr/bin/env php
<?php
if (version_compare('5.6', PHP_VERSION) > 0) {
    echo 1;
} else {
    echo 0;
}
