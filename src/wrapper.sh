#!/usr/bin/env bash
#set -x
my_dir=$(realpath $(dirname ${0}))
cd "$my_dir"
php_bin=`which php`
invoker_path='invoker.php'
params[0]="GET /api/users/{user_id}/count_pending_messages"
params[1]="GET /api/users/{user_id}/get_messages"
params[2]="GET /api/users/{user_id}/get_friends_progress"
params[3]="GET /api/users/{user_id}/get_friends_score"
params[4]="POST /api/users/{user_id}"
params[5]="GET /api/users/{user_id}"
params[6]="POST /version_api/files"

for param in "${params[@]}"
do
echo "Invoking: $php_bin $invoker_path $param"
"$php_bin" "$invoker_path" $param
echo
echo
done

cd - &> /dev/null;
