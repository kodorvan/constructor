#!/bin/bash

if [ -d author/project ]; then
	mv author/project author/constructor
fi

if [ -d author ]; then
	mv author kodorvan
fi

for i in kodorvan/constructor/system/settings/*.sample; do
  echo $i;
  if [ ! -f "${i/.sample/}" ]; then
    cp "$i" "${i/.sample/}";
    echo ${i/.sample/};
  fi
done