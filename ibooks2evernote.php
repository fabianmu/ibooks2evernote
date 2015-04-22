<?php
/*
 *	iBooks notes to Evernote converter
 *  by Joris Witteman <joris@jor.is>
 *  
 *  Reads the iBooks Annotations library on your Mac and exports
 *  them, tagged with their respective book title and imported in
 *  separate notebooks.
 *
 *  Usage:
 *  
 *	Move this script to the top of your personal home directory on your Mac.
 *  This is the folder that has your name, which the Finder opens if you
 *  click on the Finder icon in the Dock.
 *
 *  To export your notes to Evernote:
 *  
 *  1. Run the following command in the Terminal:
 *
 *     php ./ibooks2evernote.php
 *    
 *  2. Open the newly created "iBooks exports for Evernote" folder from your
 *     home folder, open each file in there, Evernote will open and start 
 *     importing your notes.
 *
 */
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
// Default file locations for required iBooks data 
define('RESULT_DIRECTORY_NAME',"iBooks exports for Evernote");
define('BOOKS_DATABASE_DIRECTORY','./Library/Containers/com.apple.iBooksX/Data/Documents/BKLibrary');
define('NOTES_DATABASE_DIRECTORY','./Library/Containers/com.apple.iBooksX/Data/Documents/AEAnnotation');


if(file_exists(RESULT_DIRECTORY_NAME)){
	die("The destination folder for the exports already exists on your Mac.\nPlease move that one out of the way before proceeding.\n");
}

// Verify presence of iBooks database

if(!file_exists(BOOKS_DATABASE_DIRECTORY)){
	die("Sorry, couldn't find an iBooks Library on your Mac. Have you put any books in there?\n");
}else{
	if(!$path = exec('ls '.BOOKS_DATABASE_DIRECTORY."/*.sqlite")){
		die("Could not find the iBooks library database. Have you put any books in there?\n");
	}else{
		define('BOOKS_DATABASE_FILE',$path);
	}
}


// Verify presence of iBooks notes database

if(!file_exists(NOTES_DATABASE_DIRECTORY)){
	die("Sorry, couldn't find any iBooks notes on your Mac. Have you actually taken any notes in iBooks?\n");
}else{
	if(!$path = exec('ls '.NOTES_DATABASE_DIRECTORY."/*.sqlite")){
		die("Could not find the iBooks notes database. Have you actually taken any notes in iBooks?\n");
	}else{
		define('NOTES_DATABASE_FILE',$path);
	}
}


// Fire up a SQLite parser

class MyDB extends SQLite3
{
  function __construct($FileName)
  {
     $this->open($FileName);
  }
}


// Retrieve any books.

$books = array();

$booksdb = new MyDB(BOOKS_DATABASE_FILE);

if(!$booksdb){
  echo $booksdb->lastErrorMsg();
} 

$res = $booksdb->query("
			SELECT
				ZASSETID,
				ZTITLE AS Title,
				ZAUTHOR AS Author
			FROM ZBKLIBRARYASSET
			WHERE ZTITLE IS NOT NULL");

while($row = $res->fetchArray(SQLITE3_ASSOC) ){
	$books[$row['ZASSETID']] = $row;
}

$booksdb->close();

if(count($books)==0) die("No books found in your library. Have you added any to iBooks?\n");

// Retrieve the notes.

$notesdb = new MyDB(NOTES_DATABASE_FILE);

if(!$notesdb){
  echo $notesdb->lastErrorMsg();
} 

$notes = array();

$res = $notesdb->query("
			SELECT
				ZANNOTATIONREPRESENTATIVETEXT as BroaderText,
				ZANNOTATIONSELECTEDTEXT as SelectedText,
				ZANNOTATIONNOTE as Note,
				ZFUTUREPROOFING5 as Chapter,
				ZANNOTATIONCREATIONDATE as Created,
				ZANNOTATIONMODIFICATIONDATE as Modified,
				ZANNOTATIONASSETID
			FROM ZAEANNOTATION
			WHERE ZANNOTATIONSELECTEDTEXT IS NOT NULL
			ORDER BY ZANNOTATIONASSETID ASC,Created ASC");

while($row = $res->fetchArray(SQLITE3_ASSOC) ){
	$notes[$row['ZANNOTATIONASSETID']][] = $row;
}

$notesdb->close();


if(count($notes)==0) die("No notes found in your library. Have you added any to iBooks?\n\nIf you did on other devices than this Mac, make sure to enable iBooks notes/bookmarks syncing on all devices.");


mkdir(RESULT_DIRECTORY_NAME);
chdir(RESULT_DIRECTORY_NAME);

$i=0;
$j=0;
$b=0;

foreach($notes as $AssetID => $booknotes){
			
	$Body = '<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE en-export SYSTEM "http://xml.evernote.com/pub/evernote-export3.dtd">
	<en-export export-date="'.@strftime('%Y%m%dT%H%M%S',time()).'" application="iBooks2Evernote" version="iBooks2Evernote Mac 0.0.1">';

	$Title  = $books[$AssetID]['Title'];

	$j = 0;

	foreach($booknotes as $note){
		
		if(empty($note['BroaderText']?$note['BroaderText']:$note['SelectedText'])) continue;
		
		if($j==0)$b++;
		$i++;
		$j++;
		
		$Body .='
<note><title>'.($note['BroaderText']?$note['BroaderText']:$note['SelectedText']).'</title><content><![CDATA[<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">
<en-note>
<div><span style="color: rgb(169, 169, 169);"><span style="font-size: 12px;">From chapter: '.$note['Chapter'].'</span></span></div>
<div>'.$note['Note'].'</div>
</en-note>
]]></content><created>'.@strftime('%Y%m%dT%H%M%S',@strtotime("2001-01-01 +". $note['Created']." seconds")).'</created><updated>'.@strftime('%Y%m%dT%H%M%S',@strtotime("2001-01-01 +". $note['Modified']." seconds")).'</updated><tag>'.$Title.'.</tag><note-attributes><author>joris@jor.is</author><source>desktop.mac</source><reminder-order>0</reminder-order></note-attributes></note>';



	}
	
	$Body .='
	</en-export>
	';
	
	file_put_contents($Title.".enex", $Body);
}

echo "Done! Exported $i notes into $b separate export files in the '".RESULT_DIRECTORY_NAME."' folder.\n\n";


