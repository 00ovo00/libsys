<!-- -CheckIsReserved
 if(isReversed == true)
	ReserveInfoChange(barcodeNum);
-ReserveInfoChange(barcodeNum)
 BookReserveControl.php로 연결, 도서 예약 목록의 첫 번째 레코드는 삭제, reserveNum--
- BorrowBookInfoRegister(userID, barcodeNum, borrowDate)
 UserDB에 userID, barcodeNum, borrowDate저장
- SuccessBookBorrow(userID, barcodeNum, borrowDate)
  BorrowBook.html에 userID, barcodeNum, borrowDate 전송

-UpdateReadingGraph(userID, barcodeNum, borrowDate)
 ReadingGraphControl.php로 대출 기록 레코드 정보 전송
-UpdateRecommendData(userID, barcodeNum)
 RecommendControl.php로 대출 기록 레코드 정보 전송 -->

 <?php
// UserDB와 연결
function connect_UserDB($ID)
{
    $user_database = "USER_INFO";
    $connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($user_database, $connect);
    ck_user($ID);
    mysql_close($connect);
}
// BookDB와 연결
function connect_BookDB($bNum)
{
    $book_database = "BOOK_INFO";
    $connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($book_database, $connect);
    // $query="select * from book";
    // $bookdata = mysql_query($query, $connect);
    mysql_close($connect);
}
// RecordDB와 연결
function connect_RecordDB($bNum, $ID)
{
    $record_database = "RECORD_INFO";
    $connect=mysql_connect('192.168.0.3','lee', 'pass')or die("mySQL 서버 연결 Error!");
    mysql_select_db($record_database, $connect);
    $bDate=date("Ymd");     // 대출일자에 자동으로 현재 일자를 받아와 입력, 입력값예)20221114
    $rDate=$bDate+14;       // 반납해야하는 일자를 대출일자에 +14하여 계산
    $query = "insert into BORROWRECORD values('', '$ID', '$bnum', '$bDate', '$rDate')";
    // borrownum(대출기록식별번호)는 table 생성시 auto_increment로 자동 생성하므로 공백으로 남김
    // 1, 2, 3... 순으로 추가
    echo "$query"; 
    mysql_close($connect);
}
// 출력하는 함수
function print_result($bNum, $ID, $bDate, $rDate)
{

}
// 사용자 유효성 검사
function ck_user($ID)
{
    
}

// BorrowBook에서 전송받은 데이터 변환
 $ID = $_POST["userID"];
 $bNum = $_POST["barcodeNum"];

 
?>