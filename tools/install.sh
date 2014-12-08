#!/bin/bash
oldpwd=`pwd`
root=`git rev-parse --show-toplevel`
cp $root/tools/hooks/* $root/.git/hooks/
cd $oldpwd
