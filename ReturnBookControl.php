<?php
// UserDB와 연결
function connect_user_table($ID)
{
    global $connect;
    global $userData;
    global $userNum;
    
    $query="select * from user";
    $userData = mysql_query($query, $connect);
    $userNum = mysql_num_rows($userData);

}
// BookDB와 연결
function connect_book_table($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;

    $query="select * from book";
    $bookData = mysql_query($query, $connect);
    $bookNum = mysql_num_rows($bookData);

}
// RecordDB와 연결, BORROW_RECORD 테이블 데이터 가져옴
function connect_borrow_record_table($bNum)
{
    global $connect;
    global $borrowRecordData;
    global $borrowRecordNum;
  
    $query="select * from BORROW_RECORD where BARCODENUM='{$bNum}'";    // 대출 DB에서 bNum과 일치하는 데이터만 연결
    $borrowRecordData = mysql_query($query, $connect);
    $borrowRecordNum = mysql_num_rows($borrowRecordData);
}
// 연결된 대출 기록 테이블에서 필요한 데이터를 로드
function load_borrow_record_table($ID, $bNum, $bDate, $rDate)
{
    global $ID;
    global $bNum;
    global $bDate;
    global $rDate;
    global $borrowRecordData;
    global $borrowRecordNum;

    connect_borrow_record_table($bNum);

    for($i=0; $i<$borrowRecordNum; $i++) {
        $borrowRecord = mysql_fetch_row($borrowRecordData);
        if($borrowRecord[0] != $borrowNum) { // 가장 최근에 대출 기록된 row 정보를 사용
            $ID = $borrowRecord[2];
            $bDate = $borrowRecord[3];
            $rDate = $borrowRecord[4];
        }
    }
}
// 반납 테이블과 연결하고 반납 정보 기록
function connect_return_record_table($ID, $bNum, $bDate, $rDate, $rrDate)
{
    global $connect;
    global $returnRecordData;

    load_borrow_record_table($ID, $bNum, $bDate, $rDate);

    $query = "insert into RETURN_RECORD values('', '$bNum', '$ID', '$bDate', '$rDate', '$rrDate', '')";
    $returnRecordData = mysql_query($query, $connect);
}

 // 사용자 유효성 검사
function ck_user($ID)
{
    global $userData;
    global $userNum;
    global $rrDate;

    connect_user_table($ID);

    if($ID == null) {
        print"<center>사용자 아이디를 입력해주세요.</center>";
        return false;
    }
    else
    {
        // 데이터베이스 레코드들을 차례로 반복하여 검사
        for($i=0; $i<$userNum; $i++) {
            $userRecord = mysql_fetch_row($userData);
            if($ID == $userRecord[0]){     // 사용자 정보 데이터베이스의 첫번째 열 정보가 ID값이라고 설정했을때
                                            // 사용자 정보가 존재하면
                // 연체 정보 확인
                $overdueCount = $userRecord[9] - ($rrDate - $userRecord[10]);    // 마지막 반납일자와 연체일수를 계산하여 남은 연체일수 업데이트
                                                                                // 업데이트할 연체일수  = 이전 연체일수 - (현재날짜 - 마지막으로 반납한 날짜)
                if($overdueCount > 0) {     // 연체일수가 남아있으면
                    $query = "update user set OVERDUECOUNT='{$overdueCount}' where userid='{$userRecord[0]}'";
                    mysql_query($query, $connect);  // 남은 연체일수 업데이트
                }
                else {                      // 연체일수가 0이하가 되면
                    $overdueCount = 0;      // 연체일수를 0으로 설정(음수값 허용 x)
                    $query = "update user set OVERDUECOUNT='{$overdueCount}' where userid='{$userRecord[0]}'";
                    mysql_query($query, $connect);
                    $userRecord[7] = true;  // 대출 가능 상태로 변경
                    $query = "update user set ISENABLE='{$userRecord[3]}' where ID='{$userRecord[0]}'";
                    mysql_query($query, $connect);
                }
                if ($userRecord[7] == true)     // 사용자가 대출 가능 상태이면
                    return true;
                else {                           // 사용자가 대출 불가 상태이면
                    print"<center>대출 불가 상태입니다.</center>";
                    return false;
                }      
            }
        }
        print"<center>등록되지 않은 사용자입니다.</center>";
        return false;
    }
}
// 도서 유효성 검사
function ck_book($bNum)
{
    global $bookData;
    global $bookNum;

    connect_book_table($bNum);
    
    if($bNum == null) {
        print"<center>바코드 번호를 입력해주세요.</center>";
        return false;
    }
    else
    {
        // 데이터베이스 레코드들을 차례로 반복하여 검사
        for($i=0; $i<$bookNum; $i++) {
            $bookRecord = mysql_fetch_row($bookData);
            if($bNum == $bookRecord[0])       // 도서 정보 데이터베이스의 첫번째 열 정보가 바코드 번호라고 설정했을때
                                            // 도서 정보가 존재하면
                if ($bookRecord[7] == true)     // 도서가 반납 가능 상태이면
                    return true;
                else{                           // 도서가 반납 불가 상태이면
                    print"<center>반납 불가 도서입니다.</center>";
                    return false;
                }
        }
        print"<center>등록되지 않은 도서입니다.</center>";
        return false;
    }
}
function update_user_table($ID)
{
    global $connect;
    global $userData;
    global $userNum;
    global $rDate;
    global $rrDate;

    connect_user_table($ID);

    for($i=0; $i<$userNum; $i++) {
        $userRecord = mysql_fetch_row($userData);
        if($ID == $userRecord[0]){     // 사용자 정보 데이터베이스의 첫번째 열 정보가 ID값이라고 설정했을때
                                        // 사용자 정보가 일치하면
            $userRecord[8]--;           // 대출 권수 감소
            $query = "update user set BORROWCOUNT='{$userRecord[4]}' where userid='{$userRecord[0]}'";
            mysql_query($query, $connect);
            $userRecord[10] = $rrDate;   // 최근 반납 일자 기록
            $query = "update user set LASTRETURNDATE='{$userRecord[6]}' where userid='{$userRecord[0]}'";
            mysql_query($query, $connect);
            $overdueCount = $rrDate-$rDate;    // 연체 여부 확인
            if($overdueCount > 0) {
                $userRecord[7] = false;  // 대출 불가 상태로 설정
                $query = "update user set ISENABLE='{$userRecord[7]}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);
                $userRecord[9] = $overdueCount;    // 연체 일수 기록
                $query = "update user set OVERDUECOUNT='{$userRecord[9]}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);
            }
            if($userRecord[8] < 10 && $overdueCount < 0) {   // 최대 대출 권수 미만이면
                $userRecord[7] = true;  // 대출 가능 상태로 설정
                $query = "update user set ISENABLE='{$userRecord[7]}' where userid='{$userRecord[0]}'";
                mysql_query($query, $connect);
            }
        }      
    }
}
function update_book_table($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;

    connect_book_table($bNum);

    for($i=0; $i<$bookNum; $i++) {
        $bookRecord = mysql_fetch_row($bookData);
        if($bNum == $bookRecord[0])       // 도서 정보 데이터베이스의 첫번째 열 정보가 바코드 번호라고 설정했을때
                                        // 도서 정보가 일치하면
            $bookRecord[7] = false;      // 상태를 대출 가능으로 변경
            $query = "update book set ISBORROW='{$bookRecord[7]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
    }

}
// 출력하는 함수
function print_result($bNum, $ID, $bDate, $rDate)
{

}

// ReturnBook에서 전송받은 데이터 변환
 $bNum = $_POST["barcodeNum"];
 $rrDate=date("Ymd");       // real return Date 실제 반납 일자
 $isBook = false;

 $database = "library_management";
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL 서버 연결 Error!");
 mysql_select_db($database, $connect);

 $isBook = ck_book($bNum);

  if($isBook == true) {     // 도서 정보가 유효한 경우에만
    load_borrow_record_table($ID, $bNum, $bDate, $rDate);   // 대출 기록 DB에서 도서에 관한 정보 호출
    connect_return_record_table($ID, $bNum, $bDate, $rDate, $rrDate);      // 반납 승인하고 반납 테이블에 기록
    update_user_table($ID);     // 대출 정보 사용자 DB에 업데이트
    update_book_table($bNum);   // 대출 정보 도서 DB에 업데이트  
  }

 mysql_close($connect);

?>