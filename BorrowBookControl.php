<?php
// UserDB와 연결
function connect_UserDB($ID)
{
    $user_database = "USER_INFO";
    //$connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($user_database, $connect);
    $query="select * from user";
    $userData = mysql_query($query, $connect);
    $userNum = mysql_num_rows($userData);    
    //ck_user($ID);
    //mysql_close($connect);
}
// BookDB와 연결
function connect_BookDB($bNum)
{
    $book_database = "BOOK_INFO";
    //$connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($book_database, $connect);
    $query="select * from book";
    $bookData = mysql_query($query, $connect);
    $bookNum = mysql_num_rows($bookData);    
    //ck_book($bNum);
    //mysql_close($connect);
}
// RecordDB와 연결
function connect_RecordDB($bNum, $ID)
{
    $record_database = "RECORD_INFO";
   // $connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($record_database, $connect);
    $query = "insert into BORROWRECORD values('', '$ID', '$bnum', '$bDate', '$rDate')";
    // borrownum(대출기록식별번호)는 table 생성시 auto_increment로 자동 생성하므로 공백으로 남김
    // 1, 2, 3... 순으로 추가
    echo "$query"; 
    //mysql_close($connect);
}
// 출력하는 함수
function print_result($bNum, $ID, $bDate, $rDate)
{

}
// 사용자 유효성 검사
function ck_user($ID)
{
    if($ID == null) {
        print"<center>사용자 아이디를 입력해주세요.</center>";
        return false;
    }
    else
    {
        // 데이터베이스 레코드들을 차례로 반복하여 검사
        for($i=0; $i<$userNum; $i++) {
            $userRecord = mysql_fetch_row($userData);
            if($ID == $userRecord[0])       // 사용자 정보 데이터베이스의 첫번째 열 정보가 ID값이라고 설정했을때
                return true;
        }
        print"<center>등록되지 않은 사용자입니다.</center>";
        return false;
    }
}
// 도서 유효성 검사
function ck_book($bNum)
{
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
                return true;
        }
        print"<center>등록되지 않은 도서입니다.</center>";
        return false;
    }
}

// BorrowBook에서 전송받은 데이터 변환
 $ID = $_POST["userID"];
 $bNum = $_POST["barcodeNum"];
 $bDate=date("Ymd");     // 대출일자에 자동으로 현재 일자를 받아와 입력, 입력값 예)20221114
 $rDate=$bDate+14;       // 반납해야하는 일자를 대출일자에 +14하여 계산
 
 $connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");

 connect_UserDB($ID);
 connect_BookDB($bNum);

 $isUser = ck_user($ID);
 $isBook = ck_book($bNum);

 if($isUser == true && $isBook == true) {
    connect_RecordDB($bNum, $ID);
    print_result($bNum, $ID, $bDate, $rDate);
 }
 mysql_close($connect);

?>