#!/usr/bin/env php
<?php
if (version_compare('7.0', PHP_VERSION) < 0 && version_compare('7.1', PHP_VERSION) > 0) {
    echo 1;
} else {
    echo 0;
}
