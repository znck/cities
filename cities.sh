#!/usr/bin/env bash

while IFS=, read city code state
do
    file=$(echo ${state}.php | tr -d '\r')
    path=data/en/IN/${file}
    if ! [ -f ${path} ]; then
        echo "Creating ${path}... $state";
        echo '<?php return [];' | cat > ${path};
    fi
    sed -i '' -e "s/\]\;/\'$code\' => \'$city\',\]\;/g" "$path";
done < "${1}"
