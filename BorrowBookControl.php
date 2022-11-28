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

    $isUser = ck_user($ID);
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

    $isBook = ck_book($bNum);
}
// RecordDB와 연결
function connect_RecordDB($ID, $bNum, $bDate, $rDate)
{
    global $connect;
    $record_database = "record";
    mysql_select_db($record_database, $connect);
    $query = "insert into BORROW_RECORD values('', '$ID', '$bNum', '$bDate', '$rDate')";
    // borrownum(대출기록식별번호)는 table 생성시 auto_increment로 자동 생성하므로 공백으로 남김
    // 1, 2, 3... 순으로 추가
    mysql_query($query,$connect);
}
 // 사용자 유효성 검사
function ck_user($ID)
{
    global $userData;
    global $userNum;

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
                if ($bookRecord[5] == true)     // 도서가 대출 가능 상태이면
                    return true;
                else{                           // 도서가 대출 불가 상태이면
                    print"<center>대출 불가 도서입니다.</center>";
                    return false;
                }
        }
        print"<center>등록되지 않은 도서입니다.</center>";
        return false;
    }
}
function update_userDB()
{

}
function update_bookDB()
{
    
}
// 출력하는 함수
function print_result($bNum, $ID, $bDate, $rDate)
{

}

// BorrowBook에서 전송받은 데이터 변환
 $ID = $_POST["userID"];
 $bNum = $_POST["barcodeNum"];
 $bDate=date("Ymd");     // 대출일자에 자동으로 현재 일자를 받아와 입력, 입력값 예)20221114     
 $rDate=date("Ymd",strtotime($day."+14 day"));  // 반납해야하는 일자를 대출일자에 +14하여 계산

 $isUser = false;
 $isBook = false;
 $userData;
 $userNum;
 
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL 서버 연결 Error!");
 connect_UserDB($ID);
 connect_BookDB($bNum);

 
 if($isUser == true && $isBook == true) {
    connect_RecordDB($ID, $bNum, $bDate, $rDate);
   // print_result($bNum, $ID, $bDate, $rDate);
 }
 mysql_close($connect);

?>