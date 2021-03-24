#!/bin/bash
if [ -t 0 ] && [ -t 1 ]; then
    USE_TTY=t
else
    USE_TTY=
fi
docker run --rm -${USE_TTY}i -v $HOME:/root -v `pwd`:/workdir -w /workdir cloudobjects/cli $*