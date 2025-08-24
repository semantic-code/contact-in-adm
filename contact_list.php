<?php
$sub_menu = "500100";
require_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '빠른상담';
require_once './admin.head.php';

//쿼리 초기화
$sca_sql = "";
$search_sql = "";

//페이징 가져오기
$page = $_GET['page']  ?: 1;

$bo_table = "contact";
$board = get_board_db($bo_table);
$target_table = $g5['write_prefix'].$bo_table;

//카테고리
$sca = (isset($_GET['sca']) && $_GET['sca'] !== '') ? trim($_GET['sca']) : '';
if($sca) $sca_sql.= "AND ca_name = '{$sca}' ";

//검색__작성중 sfl, stx
if($_GET['sfl']){
    $sfl = htmlspecialchars(addslashes(urldecode(trim($_GET['sfl']))), ENT_QUOTES);
    $stx = htmlspecialchars(addslashes(urldecode(trim($_GET['stx']))), ENT_QUOTES);

    if (!in_array($sfl, array('wr_name', 'wr_sort', 'wr_phone'))) {
        alert('잘못된 경로로 접속하셨습니다.\n정상적인 방법으로 접속하여 주시기 바랍니다.');
        exit;
    }

    if($sfl === 'wr_name'){
        $search_sql.= "AND INSTR(wr_name, '{$stx}')  > 0 ";
    }
    if($sfl === 'wr_sort'){
        $search_sql.= "AND INSTR(wr_sort, '{$stx}')  > 0 ";
    }
    if($sfl === 'wr_phone'){
        $search_sql.= "AND INSTR(wr_phone, '{$stx}')  > 0 ";
    }
}

//총 레코드 수
if($sca || $stx || $stx === '0'){
    $sql = " SELECT COUNT(*) AS cnt FROM {$target_table} WHERE (1) AND wr_is_comment = 0 {$sca_sql} {$search_sql} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
}else{
    $total_count = $board['bo_count_write'];
}

//페이징
$page_rows = $board['bo_page_rows'];
$total_page = ceil($total_count / $page_rows);
$start_record = ($page - 1) * $page_rows;
$paging = get_paging(5, $page, $total_page, G5_ADMIN_URL."/contact_list.php?sca=".$sca."&sfl=".$sfl."&stx=".$stx);

//정렬
$order_by = $board['bo_sort_field'] ? "ORDER BY {$board['bo_sort_field']}" : "ORDER BY  wr_num, wr_reply";

$str_sql = "SELECT * FROM {$target_table} WHERE (1) AND wr_is_comment = 0 {$sca_sql} {$search_sql} {$order_by} LIMIT {$start_record}, {$page_rows}";
$result = sql_query($str_sql);

$index = 0;
while ($row = sql_fetch_array($result)){
    $row_data = get_list($row, $board, '', '');
    $row_data['num'] = intval(($total_count) - ($start_record + $index));
    $list[] = $row_data;
    $index++;
}

$arr_cate = explode('|', $board['bo_category_list']);
?>

<style>
    /* 접근성용 숨김 라벨 */
    .sr-only{
        position:absolute; width:1px; height:1px; margin:-1px; padding:0;
        overflow:hidden; clip:rect(0,0,0,0); border:0;
    }

    /* 상단 툴바 컨테이너 */
    .admin-toolbar{
        display:flex;
        align-items:flex-start;           /* 탭과 폼 위쪽 정렬 */
        justify-content:space-between;
        gap:12px;
        padding:12px 16px;
        border:1px solid #e5e7eb;
        border-radius:8px;
        background:#fafafa;
        margin:10px 0 15px;
    }

    /* 상태 탭 */
    .admin-tabs{
        display:flex; gap:6px; list-style:none; padding:0; margin:0;
    }
    .admin-tabs .tab{
        display:inline-block;
        padding:5px 10px;
        border:1px solid #d1d5db;
        border-radius:6px;
        background:#fff;
        color:#374151; text-decoration:none; font-size:14px;
    }
    .admin-tabs .tab:hover{ background:#f3f4f6; }
    .admin-tabs .tab.is-active{
        background:#3f51b5; color:#fff; border-color:#1d4ed8;
    }

    /* 검색 폼 (카테고리 줄 + 검색 줄을 세로 배치) */
    .admin-search{
        display:flex;
        flex-direction:column;
        gap:8px;
        flex:1;
    }

    /* 각 줄 */
    .admin-search__row{
        display:flex;
        align-items:center;
        gap:8px;
    }

    /* 카테고리 전용 줄: 폭 넓게 */
    .admin-search__row--full select{
        min-width:260px;
    }

    /* 셀렉트/인풋 공통 */
    .admin-search .sel,
    .admin-search .inp{
        height:30px;
        border:1px solid #d1d5db;
        border-radius:6px;
        padding:0 10px;
        background:#fff;
    }
    .admin-search .inp{
        min-width:220px;
    }
    .admin-search .inp:focus,
    .admin-search .sel:focus{
        border-color:#6366f1;
        box-shadow:0 0 0 3px rgba(99,102,241,.15);
        outline:0;
    }

    /* 버튼 */
    .btn{
        display:inline-flex; align-items:center; justify-content:center;
        height:30px; padding:0 12px; border-radius:6px;
        text-decoration:none; cursor:pointer; user-select:none;
    }
    .btn-primary{ background:#3f51b5; color:#fff; border:1px solid #1d4ed8; }
    .btn-primary:hover{ background:#1d4ed8; }
    .btn-line{ background:#fff; color:#374151; border:1px solid #d1d5db; }
    .btn-line:hover{ background:#f3f4f6; }

    /* 표와 간격 정리(선택) */
    .tbl_head01{ margin-top:10px; }

</style>

<section>
    <?php if($board['bo_use_category']): ?>
    <div class="admin-toolbar">
        <!-- 카테고리 탭 -->
        <ul class="admin-tabs" role="tablist">
            <li><a href="?sca=&sfl=<?= $sfl ?>&stx=<?= $stx ?>" class="tab <?= ($sca === '') ? 'is-active' : '' ?>">전체</a></li>
            <?php foreach ($arr_cate as $cate): ?>
            <?php $is_active = $sca === $cate ? 'is-active' : '' ; ?>
            <li><a href="?sca=<?= $cate?>&sfl=<?= $sfl ?>&stx=<?= $stx ?>" class="tab <?= $is_active ?>"><?= $cate?></a></li>
            <?php endforeach; ?>
        </ul>    
    </div>
    <?php endif; ?>
    
    <!-- 검색 폼 -->
    <form class="admin-search" method="get" action="./contact_list.php" style="display: block;">
        <input type="hidden" name="sca" value="<?= $sca ?>">
        <!-- 검색 줄 -->
        <div class="admin-search__row">
            <label for="search_field" class="sr-only">검색조건</label>
            <select name="sfl" id="search_field" class="sel">
                <option value="wr_name" <?= get_selected('wr_name', $sfl) ?>>이름</option>
                <option value="wr_dept" <?= get_selected('wr_dept', $sfl) ?>>문의유형</option>
                <option value="wr_phone" <?= get_selected('wr_phone', $sfl) ?>>전화번호</option>
            </select>
            <input type="text" name="stx" id="search_text" class="inp" placeholder="검색어 입력" value="<?= $stx ?>">
            <button type="submit" class="btn btn-primary">검색</button>
            <a href="./contact_list.php" class="btn btn_02">초기화</a>
        </div>
    </form>

    <div class="tbl_head01">
        <form id="contact-form" onsubmit="return false">
            <table>
                <thead>
                <tr style="">
                    <th>번호</th>
                    <th>이름</th>
                    <th>문의유형</th>
                    <th>전화번호</th>
                    <th>상담내용</th>
                    <th>날짜</th>
                    <th>상태변경</th>
                    <th>간단메모</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $row): ?>
                    <tr>
                        <td><?= $row['num'] ?></td>
                        <td><?= $row['wr_name'] ?></td>
                        <td><?= $row['wr_sort'] ?></td>
                        <td><?= $row['wr_phone'] ?></td>
                        <td><?= htmlspecialchars($row['wr_content'] ?? '', ENT_QUOTES) ?></td>
                        <td><?= date('Y-m-d H:i:s', strtotime($row['wr_datetime'])) ?></td>
                        <td>
                            <?php if($board['bo_category_list'] && $board['bo_use_category']): ?>
                                <select id="ca_name" name="ca_name" data-wr-id="<?= $row['wr_id'] ?>">
                                    <?php foreach ($arr_cate as $cate): ?>
                                    <option value="<?= $cate ?>" <?= get_selected($row['ca_name'], $cate) ?>><?= $cate ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif;?>
                        </td>
                        <td style="display: flex;">
                            <input type="text" name="wr_memo" value="<?= $row['wr_memo'] ?>" style="width: 90%; margin-right: .5rem;">
                            <div style="display: flex; gap: .5rem; width: 235px;">
                                <button type="button" class="btn btn_03 btn_memo_update" data-wr-id="<?= $row['wr_id'] ?>">메모저장</button>
                                <button type="button" class="btn btn_01 btn_delete" data-wr-id="<?= $row['wr_id'] ?>">상담글삭제</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>

    <div class="paging"><?= $paging ?></div>

</section>

<script>
    const bo_table = <?= json_encode($bo_table) ?>;

    $(document).ready(function (){
        //상태변경
        $(document).on('change', '#ca_name', function(){
            const mode = 'update_ca_name';
            const wr_id = $(this).data('wr-id');
            const ca_name = $(this).val();

            $.post("contact_update.php", {mode, bo_table, wr_id, ca_name}, function(data){
                if(data.state === 'success_update_ca_name'){
                    self.location.reload();
                }
            }, 'json');
        });

        //메모저장
        $(document).on('click', '.btn_memo_update', function(){
            const mode = 'update_memo';
            const wr_id = $(this).data('wr-id');
            const memo = $(this).closest('td').find('input[type="text"]').val();

            $.post("contact_update.php", {mode, bo_table, wr_id, memo}, function(){
                if(data.state === 'success_update_memo'){
                    self.location.reload();
                }
            }, 'json');
        });

        //상담글삭제
        $(document).on('click', '.btn_delete', function(){
            if(!confirm("글을 삭제하시겠습니까?\n한번 삭제한 글은 복구할 수 없습니다.")) return false;
            const mode = 'delete';
            const wr_id = $(this).data('wr-id');

            $.post("contact_update.php", {mode, bo_table, wr_id}, function(data){
                if(data.state === 'success_delete'){
                    self.location.reload();
                }
            }, 'json');
        });
    });
</script>

<?php
require_once './admin.tail.php';
