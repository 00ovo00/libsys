<?php
// UserDB�� ����
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
// BookDB�� ����
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
// RecordDB�� ����
function connect_RecordDB($ID, $bNum, $bDate, $rDate)
{
    global $connect;
    $record_database = "record";
    mysql_select_db($record_database, $connect);
    $query = "insert into BORROW_RECORD values('', '$ID', '$bNum', '$bDate', '$rDate')";
    // borrownum(�����Ͻĺ���ȣ)�� table ������ auto_increment�� �ڵ� �����ϹǷ� �������� ����
    // 1, 2, 3... ������ �߰�
    mysql_query($query,$connect);
}
 // ����� ��ȿ�� �˻�
function ck_user($ID)
{
    global $userData;
    global $userNum;

    connect_UserDB($ID);

    if($ID == null) {
        print"<center>����� ���̵� �Է����ּ���.</center>";
        return false;
    }
    else
    {
        // �����ͺ��̽� ���ڵ���� ���ʷ� �ݺ��Ͽ� �˻�
        for($i=0; $i<$userNum; $i++) {
            $userRecord = mysql_fetch_row($userData);
            if($ID == $userRecord[0]){     // ����� ���� �����ͺ��̽��� ù��° �� ������ ID���̶�� ����������
                                            // ����� ������ �����ϸ�
                if ($userRecord[3] == true)     // ����ڰ� ���� ���� �����̸�
                    return true;
                else{                           // ����ڰ� ���� �Ұ� �����̸�
                    print"<center>���� �Ұ� �����Դϴ�.</center>";
                    return false;
                }      
            }
        }
        print"<center>��ϵ��� ���� ������Դϴ�.</center>";
        return false;
    }
}
// ���� ��ȿ�� �˻�
function ck_book($bNum)
{
    global $bookData;
    global $bookNum;

    connect_BookDB($bNum);
    if($bNum == null) {
        print"<center>���ڵ� ��ȣ�� �Է����ּ���.</center>";
        return false;
    }
    else
    {
        // �����ͺ��̽� ���ڵ���� ���ʷ� �ݺ��Ͽ� �˻�
        for($i=0; $i<$bookNum; $i++) {
            $bookRecord = mysql_fetch_row($bookData);
            if($bNum == $bookRecord[0])       // ���� ���� �����ͺ��̽��� ù��° �� ������ ���ڵ� ��ȣ��� ����������
                                            // ���� ������ �����ϸ�
                if ($bookRecord[5] == false)     // ������ ���� ���� �����̸�
                    return true;
                else{                           // ������ ���� �Ұ� �����̸�
                    print"<center>���� �Ұ� �����Դϴ�.</center>";
                    return false;
                }
        }
        print"<center>��ϵ��� ���� �����Դϴ�.</center>";
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
        if($ID == $userRecord[0]){     // ����� ���� �����ͺ��̽��� ù��° �� ������ ID���̶�� ����������
                                        // ����� ������ ��ġ�ϸ�
            $userRecord[4]++;           // ���� �Ǽ� ����
            $query = "update USER set BORROWCOUNT='{$userRecord[4]}' where ID='{$userRecord[0]}'";
            mysql_query($query, $connect);
            if($userRecord[4] >= 10) {   // �ִ� ���� �Ǽ� �̻��̸�
                $userRecord[3] = false;  // ���� �Ұ� ���·� ����
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
        if($bNum == $bookRecord[0])       // ���� ���� �����ͺ��̽��� ù��° �� ������ ���ڵ� ��ȣ��� ����������
                                        // ���� ������ ��ġ�ϸ�
            $bookRecord[5] = true;      // ���¸� ���������� ����
            $query = "update BOOK set ISBORROW='{$bookRecord[5]}' where BARCODENUM='{$bookRecord[0]}'";
            mysql_query($query, $connect);
    }

}
// ����ϴ� �Լ�
function print_result($bNum, $ID, $bDate, $rDate)
{

}

// BorrowBook���� ���۹��� ������ ��ȯ
 $ID = $_POST["userID"];
 $bNum = $_POST["barcodeNum"];
 $bDate=date("Ymd");     // �������ڿ� �ڵ����� ���� ���ڸ� �޾ƿ� �Է�, �Է°� ��)20221114     
 $rDate=date("Ymd",strtotime($day."+14 day"));  // �ݳ��ؾ��ϴ� ���ڸ� �������ڿ� +14�Ͽ� ���

 $isUser = false;
 $isBook = false;
 $userData;
 $userNum;
 
 $connect=mysql_connect('mydatabase.cojdhegxjiex.ap-northeast-2.rds.amazonaws.com','admin', '08081234')or die("mySQL ���� ���� Error!");

 $isUser = ck_user($ID);
 $isBook = ck_book($bNum);

 
 if($isUser == true && $isBook == true) {
    connect_RecordDB($ID, $bNum, $bDate, $rDate);
    update_userDB($ID);
    update_bookDB($bNum);
   // print_result($bNum, $ID, $bDate, $rDate);
 }
 mysql_close($connect);

?>