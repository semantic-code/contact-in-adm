<?php
$sub_menu = "500100";
require_once './_common.php';

$target_table = $g5['write_prefix'].$bo_table;

if($mode === 'update_ca_name'){
    $set = array("ca_name" => $ca_name);
    $sql = "UPDATE {$target_table} SET\n".build_query($set)."\nWHERE wr_id = '{$wr_id}' ";
    $update = sql_query($sql);
    if($update) {
        $arr_result = array("state" => "success_update_ca_name");
    } else {
        $arr_result = array("state" => "fail_update_ca_name");
    }
} elseif ($mode === 'update_memo'){
    $set = array("wr_memo" => $memo);
    $sql = "UPDATE {$target_table} SET\n".build_query($set)."\nWHERE wr_id = '{$wr_id}' ";
    $update = sql_query($sql);
    if($update) {
        $arr_result = array("state" => "success_update_memo");
    } else {
        $arr_result = array("state" => "fail_update_memo");
    }
} elseif ($mode === 'delete'){
    $sql = "DELETE FROM {$target_table} WHERE wr_id = '{$wr_id}' ";
    $delete = sql_query($sql);
    $count = sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write - 1 WHERE bo_table = '{$bo_table}' ");
    if($delete && $count){
        $arr_result = array("state" => "success_delete");
    } else {
        $arr_result = array("state" => "fail_delete");
    }
}

die(json_encode($arr_result));
