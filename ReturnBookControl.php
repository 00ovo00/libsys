<?php
// UserDB와 연결
function connect_UserDB($ID)
{
    global $connect;
    global $userData;
    global $userNum;
    global $isUser;

    $user_database = "user_info";
    mysql_select_db($user_database, $connect);
    $query="select * from USER";
    $userData = mysql_query($query, $connect);
    $userNum = mysql_num_rows($userData);

}
// BookDB와 연결
function connect_BookDB($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;
    global $isBook;

    $book_database = "book_info";
    mysql_select_db($book_database, $connect);
    $query="select * from BOOK";
    $bookData = mysql_query($query, $connect);
    $bookNum = mysql_num_rows($bookData);

}
// RecordDB와 연결
//function connect_borrow_recordDB($ID, $bNum, $bDate, $rDate)
function connect_borrow_recordDB($bNum)
{
    global $connect;
    global $borrowRecordData;
    global $borrowRecordNum;

    $record_database = "record";
    mysql_select_db($record_database, $connect);
    $query="select * from BORROW_RECORD where BARCODENUM='{$bNum}'";    // 대출 DB에서 bNum과 일치하는 데이터만 연결
    $borrowRecordData = mysql_query($query, $connect);
    $borrowRecordNum = mysql_num_rows($borrowRecordData);
}
function connect_return_recordDB($ID, $bNum, $bDate, $rDate, $rrDate)
{
    global $ID;
    global $bDate;
    global $rDate;
    global $connect;
    global $returnRecordData;
    global $returnRecordNum;

    load_borrow_recordDB($ID, $bNum, $bDate, $rDate);
    echo $ID;

    $record_database = "record";
    mysql_select_db($record_database, $connect);
    $query="select * from RETURN_RECORD";
    $returnRecordData = mysql_query($query, $connect);
    $returnRecordNum = mysql_num_rows($returnRecordData);
    $query = "insert into RETURN_RECORD values('', '$ID', '$bNum', '$bDate', '$rDate', '$rrDate')";
    $returnRecordData = mysql_query($query, $connect);
   // $returnRecordNum = mysql_num_rows($returnRecordData);
    // $returnRecordData = mysql_query($query, $connect);
    // $returnRecordNum = mysql_num_rows($returnRecordData);
}
// function connect_RecordDB($ID, $bNum, $bDate, $rDate)
// {
//     global $connect;
//     $record_database = "record";
//     mysql_select_db($record_database, $connect);
//     $query = "insert into BORROW_RECORD values('', '$ID', '$bNum', '$bDate', '$rDate')";
//     // borrownum(대출기록식별번호)는 table 생성시 auto_increment로 자동 생성하므로 공백으로 남김
//     // 1, 2, 3... 순으로 추가
//     mysql_query($query,$connect);
// }
function load_borrow_recordDB($ID, $bNum, $bDate, $rDate)
{
    global $ID;
    global $bDate;
    global $rDate;

    global $borrowRecordData;
    global $borrowRecordNum;

    connect_borrow_recordDB($bNum);

    //$borrowRecord = mysql_fetch_row($borrowRecordData);

    for($i=0; $i<$borrowRecordNum; $i++) {
        $borrowRecord = mysql_fetch_row($borrowRecordData);
        echo $borrowRecord[0];
        echo $borrowNum;
        if($borrowRecord[0] == $borrowNum) { // 가장 최근에 대출 기록된 row 정보를 사용
            echo $borrowRecord[0];
            echo ("aa");
            $ID = $borrowRecord[1];
            $bDate = $borrowRecord[3];
            $rDate = $borrowRecord[4];
        }
    }
    echo $ID;
    //mysql_query($query,$connect);
}
 // 사용자 유효성 검사
function ck_user($ID)
{
    global $userData;
    global $userNum;

    connect_UserDB($ID);

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
                if ($userRecord[3] == true)     // 사용자가 대출 가능 상태이면
                    return true;
                else{                           // 사용자가 대출 불가 상태이면
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

    connect_BookDB($bNum);
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
                if ($bookRecord[5] == true)     // 도서가 반납 가능 상태이면
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
function update_userDB($ID)
{
    global $connect;
    global $userData;
    global $userNum;

    connect_UserDB($ID);

    for($i=0; $i<$userNum; $i++) {
        $userRecord = mysql_fetch_row($userData);
        if($ID == $userRecord[0]){     // 사용자 정보 데이터베이스의 첫번째 열 정보가 ID값이라고 설정했을때
                                        // 사용자 정보가 일치하면
            $userRecord[4]--;           // 대출 권수 감소
            $query = "update USER set BORROWCOUNT='{$userRecord[4]}' where ID='{$userRecord[0]}'";
            mysql_query($query, $connect);
            if($userRecord[4] < 10) {   // 최대 대출 권수 미만이면
                $userRecord[3] = true;  // 대출 가능 상태로 설정
                $query = "update USER set ISENABLE='{$userRecord[3]}' where ID='{$userRecord[0]}'";
            }
        }      
    }
}
function update_bookDB($bNum)
{
    global $connect;
    global $bookData;
    global $bookNum;

    connect_BookDB($bNum);

    for($i=0; $i<$bookNum; $i++) {
        $bookRecord = mysql_fetch_row($bookData);
        if($bNum == $bookRecord[0])       // 도서 정보 데이터베이스의 첫번째 열 정보가 바코드 번호라고 설정했을때
                                        // 도서 정보가 일치하면
            $bookRecord[5] = false;      // 상태를 대출 가능으로 변경
            $query = "update BOOK set ISBORROW='{$bookRecord[5]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
    }

}
// 출력하는 함수
function print_result($bNum, $ID, $bDate, $rDate)
{

}

// ReturnBook에서 전송받은 데이터 변환
 $ID;   // borrowRecordDB에서 값을 받아옴
 $bNum = $_POST["barcodeNum"];
 $bDate;   // borrowRecordDB에서 값을 받아옴
 $rDate;   // borrowRecordDB에서 값을 받아옴
 $rrDate=date("Ymd");       // real return Date 실제 반납 일자

 //$isUser = false;
 $isBook = false;
 $userData;
 $userNum;
 $borrowRecord;
 $borrowRecordNum;
 $returnRecordData;
 $returnRecordNum;

 
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL 서버 연결 Error!");

 //$isUser = ck_user($ID);
 $isBook = ck_book($bNum);

 //if($isUser == true && $isBook == true) {
    //connect_RecordDB($ID, $bNum, $bDate, $rDate);
    //update_userDB($ID);
    //update_bookDB($bNum);
   // print_result($bNum, $ID, $bDate, $rDate);
 //}
  if($isBook == true) {
    load_borrow_recordDB($ID, $bNum, $bDate, $rDate);
    connect_return_recordDB($ID, $bNum, $bDate, $rDate, $rrDate);      
  }

  
 mysql_close($connect);

?>