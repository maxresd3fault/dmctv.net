#!/bin/sh
git config filter.hide_secrets.clean "sed -E \"s/(define\\('[^']+',')[^']*('\\);)/\\1***REDACTED***\\2/g\""
git config filter.hide_secrets.smudge "cat"