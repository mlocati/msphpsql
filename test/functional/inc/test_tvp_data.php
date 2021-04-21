<?php

$createTVPOrd = <<<ORD
CREATE TABLE TVPOrd(
    OrdNo INTEGER IDENTITY(1,1), 
    OrdDate DATETIME, 
    CustID VARCHAR(10))
ORD;

$createTVPItem = <<<ITEM
CREATE TABLE TVPItem(
    OrdNo INTEGER, 
    ItemNo INTEGER IDENTITY(1,1), 
    ProductCode CHAR(10), 
    OrderQty INTEGER, 
    SalesDate DATE, 
    Label NVARCHAR(30), 
    Price DECIMAL(5,2), 
    Photo VARBINARY(MAX))
ITEM;

$createTVPParam = <<<TYPE
CREATE TYPE TVPParam AS TABLE(
                ProductCode CHAR(10), 
                OrderQty INTEGER, 
                SalesDate DATE, 
                Label NVARCHAR(30), 
                Price DECIMAL(5,2), 
                Photo VARBINARY(MAX))
TYPE;

$createTVPOrderEntry = <<<PROC
CREATE PROCEDURE TVPOrderEntry(
        @CustID VARCHAR(10), 
        @Items TVPParam READONLY,
        @OrdNo INTEGER OUTPUT, 
        @OrdDate DATETIME OUTPUT)
AS
BEGIN
    SET @OrdDate = GETDATE(); SET NOCOUNT ON; 
    INSERT INTO TVPOrd (OrdDate, CustID) VALUES (@OrdDate, @CustID);
    SELECT @OrdNo = SCOPE_IDENTITY();
    INSERT INTO TVPItem (OrdNo, ProductCode, OrderQty, SalesDate, Label, Price, Photo)
    SELECT @OrdNo, ProductCode, OrderQty, SalesDate, Label, Price, Photo 
    FROM @Items
END;
PROC;

$callTVPOrderEntry = "{call TVPOrderEntry(?, ?, ?, ?)}";
$callTVPOrderEntryNamed = "{call TVPOrderEntry(:id, :tvp, :ordNo, :ordDate)}";

// The following gif files are some random product pictures 
// retrieved from the AdventureWorks sample database (their 
// sizes ranging from 12 KB to 26 KB)
$gif1 = 'awc_tee_male_large.gif';
$gif2 = 'superlight_black_f_large.gif';
$gif3 = 'silver_chain_large.gif';

$items = [
    ['0062836700', 367, "2009-03-12", 'AWC Tee Male Shirt', '20.75'],
    ['1250153272', 256, "2017-11-07", 'Superlight Black Bicycle', '998.45'],
    ['1328781505', 260, "2010-03-03", 'Silver Chain for Bikes', '88.98'],
];

$selectTVPItemQuery = 'SELECT OrdNo, ItemNo, ProductCode, OrderQty, SalesDate, Label, Price FROM TVPItem ORDER BY ItemNo';

///////////////////////////////////////////////////////

$createTestTVP = <<<TYPE1
CREATE TYPE TestTVP AS TABLE(
                C01 VARCHAR(255),
                C02 VARCHAR(MAX),
                C03 BIT,
                C04 SMALLDATETIME,
                C05 DATETIME2(5),
                C06 UNIQUEIDENTIFIER,
                C07 BIGINT,
                C08 FLOAT,
                C09 NUMERIC(38, 24))
TYPE1;

$createSelectTVP = <<<PROC1
CREATE PROCEDURE SelectTVP (
        @TVP TestTVP READONLY) 
        AS 
        SELECT * FROM @TVP
PROC1;

$callSelectTVP = "{call SelectTVP(?)}";

///////////////////////////////////////////////////////

$createTestTVP2 = <<<TYPE2
CREATE TYPE TestTVP2 AS TABLE(
                C01 NVARCHAR(50),
                C02 NVARCHAR(MAX),
                C03 INT,
                C04 REAL,
                C05 VARBINARY(10),
                C06 VARBINARY(MAX),
                C07 MONEY,
                C08 XML,
                C09 SQL_VARIANT)
TYPE2;

$createSelectTVP2 = <<<PROC2
CREATE PROCEDURE SelectTVP2 (
        @TVP TestTVP2 READONLY) 
        AS 
        SELECT * FROM @TVP
PROC2;

$callSelectTVP2 = "{call SelectTVP2(?)}";

///////////////////////////////////////////////////////

$createSchema = 'CREATE SCHEMA [Sales DB]';
$dropSchema = 'DROP SCHEMA IF EXISTS [Sales DB]';

$createTestTVP3 = <<<TYPE3
CREATE TYPE [Sales DB].[TestTVP3] AS TABLE(
    Review VARCHAR(MAX) NOT NULL,
    SupplierId INT,
    SalesDate DATETIME2 NULL
)
TYPE3;

$createSelectTVP3 = <<<PROC3
CREATE PROCEDURE [Sales DB].[SelectTVP3] (
        @TVP TestTVP3 READONLY) 
        AS 
        SELECT * FROM @TVP
PROC3;

$callSelectTVP3 = "{call [Sales DB].[SelectTVP3](?)}";

///////////////////////////////////////////////////////
// Common functions
///////////////////////////////////////////////////////

function dropProcSQL($conn, $procName)
{
    return "DROP PROC IF EXISTS $procName";
}

function dropTableTypeSQL($conn, $typeName)
{
    return "DROP TYPE IF EXISTS $typeName";
}

function verifyBinaryData($fp, $data)
{
    $size = 8192;
    $pos = 0;
    $matched = true;
    while (!feof($fp)) {
        $original = fread($fp, $size);
        $str = substr($data, $pos, $size);
        
        if ($original !== $str) {
            $matched = false;
            break;
        }
        $pos += $size;
    }
    
    return $matched;
}

function verifyBinaryStream($fp, $stream)
{
    $size = 8192;
    $matched = true;
    while (!feof($fp) && !feof($stream)) {
        $original = fread($fp, $size);
        $data = fread($stream, $size);
        
        if ($original !== $data) {
            $matched = false;
            break;
        }
    }
    
    return $matched;
}

?>