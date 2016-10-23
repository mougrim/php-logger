#!/usr/bin/env bash
PATH=`dirname "$(readlink -f "$0")"`/tests/bin:$PATH
# create cache dir for soft-mocks
[ -d /tmp/mocks/ ] || mkdir /tmp/mocks/
