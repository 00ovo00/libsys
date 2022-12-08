﻿<?php
// UserDB와 연결
function connect_UserDB($ID)
{
    global $connect;
    global $userData;
    global $userNum;

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

    $book_database = "book_info";
    mysql_select_db($book_database, $connect);
    $query="select * from BOOK";
    $bookData = mysql_query($query, $connect);
    $bookNum = mysql_num_rows($bookData);

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
     global $connect;
     global $userData;
     global $userNum;
     global $rrDate;
     global $overdueCount;
 
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
                 // 연체 정보 확인
                 $overdueCount = $userRecord[5] - ($rrDate - $userRecord[6]);    // 마지막 반납일자와 연체일수를 계산하여 남은 연체일수 업데이트
                                                                                 // 업데이트할 연체일수  = 이전 연체일수 - (현재날짜 - 마지막으로 반납한 날짜)
                 if($overdueCount > 0) {     // 연체일수가 남아있으면
                     $query = "update USER set OVERDUECOUNT='{$overdueCount}' where ID='{$userRecord[0]}'";
                     mysql_query($query, $connect);  // 남은 연체일수 업데이트
                 }
                 else {                      // 연체일수가 0이하가 되면
                     $overdueCount = 0;      // 연체일수를 0으로 설정(음수값 허용 x)
                     $query = "update USER set OVERDUECOUNT='{$overdueCount}' where ID='{$userRecord[0]}'";
                     mysql_query($query, $connect);
                     $userRecord[3] = true;  // 대출 가능 상태로 변경
                     $query = "update USER set ISENABLE='{$userRecord[3]}' where ID='{$userRecord[0]}'";
                     mysql_query($query, $connect);
                 }
                 if ($userRecord[3] == true)     // 사용자가 대출 가능 상태이면
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
                if ($bookRecord[5] == false)     // 도서가 대출 가능 상태이면
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
            $userRecord[4]++;           // 대출 권수 증가
            $query = "update USER set BORROWCOUNT='{$userRecord[4]}' where ID='{$userRecord[0]}'";
            mysql_query($query, $connect);
            if($userRecord[4] >= 10) {   // 최대 대출 권수 이상이면
                $userRecord[3] = false;  // 대출 불가 상태로 설정
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
            $bookRecord[5] = true;      // 상태를 대출중으로 변경
            $query = "update BOOK set ISBORROW='{$bookRecord[5]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
            $bookRecord[6]++;           // 대출 횟수 증가
            $query = "update BOOK set BORROWEDCOUNT='{$bookRecord[6]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
    }

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
 $rrDate=date("Ymd");       // real return Date 실제 반납 일자

 $isUser = false;
 $isBook = false;
 
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL 서버 연결 Error!");

 $isUser = ck_user($ID);    
 $isBook = ck_book($bNum);

 if($isUser == true && $isBook == true) {       // 사용자와 도서 정보가 유효한 경우에만
    connect_RecordDB($ID, $bNum, $bDate, $rDate);       // 대출 승인(대출 데이터를 기록)
    update_userDB($ID);     // 대출 정보 사용자 DB에 업데이트
    update_bookDB($bNum);   // 대출 정보 도서 DB에 업데이트
   // print_result($bNum, $ID, $bDate, $rDate);
 }
 mysql_close($connect);

?>