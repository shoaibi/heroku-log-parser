#!/usr/bin/env bash
#set -x
my_dir=$(realpath $(dirname ${0}))
cd "$my_dir"
php_bin=`which php`
invoker_path='invoker.php'
params[0]="GET /api/users/100000365305385/count_pending_messages"
params[1]="GET /api/users/100004791553821/get_messages"
params[2]="GET /api/users/1518744105/get_friends_progress"
params[3]="GET /api/users/1372011780/get_friends_score"
params[4]="POST /api/users/1672481427"
params[5]="GET /api/users/1672481427"

for param in "${params[@]}"
do
echo "Invoking: $php_bin $invoker_path $param"
"$php_bin" "$invoker_path" $param
echo
echo
done

cd - &> /dev/null;
