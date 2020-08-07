#!/bin/bash

cat build/asset-manifest.json | grep -o -E "\.[0-9a-z]{8}\." | awk '{system("find ./build/static -exec sed -i \"\" -E \"s/\\" $0 "/./g\" {} \\; 2>/dev/null")}'
find ./build/static -type f | grep -E "\.[0-9a-z]{8}\." | rename "s/.[a-z0-9]{8}././g"

