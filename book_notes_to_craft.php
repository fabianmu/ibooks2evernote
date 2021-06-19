<?php
/*
 *  Apple Books (iBooks) notes to MD for Craft
 *  by Fabian Mürmann <hello@fabian.mu>
 * 
 *  based on https://github.com/jorisw/ibooks2evernote by Joris Witteman <joris@jor.is>
 *  
 *  Reads the Books Annotations library on your Mac and exports
 *  them, tagged with their respective book title and imported in
 *  separate notes.
 * 
 *  To export your notes to MD Files:
 *  
 *  1. Run the following command in the Terminal:
 *
 *     php ./book_notes_to_craft.php
 *  
 * 
 */
 
// Default file locations for required iBooks data 
define('RESULT_DIRECTORY_NAME',"export");
define('BOOKS_DATABASE_DIRECTORY','~/Library/Containers/com.apple.iBooksX/Data/Documents/BKLibrary');
define('NOTES_DATABASE_DIRECTORY','~/Library/Containers/com.apple.iBooksX/Data/Documents/AEAnnotation');

// Verify presence of iBooks database

$path = exec('ls '.BOOKS_DATABASE_DIRECTORY."/*.sqlite");
define('BOOKS_DATABASE_FILE',$path);
// if(!file_exists(BOOKS_DATABASE_DIRECTORY)){
// 	die("Sorry, couldn't find an iBooks Library on your Mac. Have you put any books in there?\n");
// }else{
// 	if(!$path = exec('ls '.BOOKS_DATABASE_DIRECTORY."/*.sqlite")){
// 		die("Could not find the iBooks library database. Have you put any books in there?\n");
// 	}else{
// 		define('BOOKS_DATABASE_FILE',$path);
// 	}
// }


// // Verify presence of iBooks notes database
$path = exec('ls '.NOTES_DATABASE_DIRECTORY."/*.sqlite");
define('NOTES_DATABASE_FILE',$path);
// if(!file_exists(NOTES_DATABASE_DIRECTORY)){
// 	die("Sorry, couldn't find any iBooks notes on your Mac. Have you actually taken any notes in iBooks?\n");
// }else{
// 	if(!$path = exec('ls '.NOTES_DATABASE_DIRECTORY."/*.sqlite")){
// 		die("Could not find the iBooks notes database. Have you actually taken any notes in iBooks?\n");
// 	}else{
// 		define('NOTES_DATABASE_FILE',$path);
// 	}
// }


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


// Create a new directory and cd into it

@mkdir(RESULT_DIRECTORY_NAME);
chdir(RESULT_DIRECTORY_NAME);

$i=0;
$j=0;
$b=0;

foreach($notes as $AssetID => $booknotes){
	if (array_key_exists($AssetID, $books)) {
		$BookTitle = $books[$AssetID]['Title'] . " by " . $books[$AssetID]['Author'];
		$Body = "# $BookTitle\nExport date: " . @strftime('%d-%m-%Y %H%:%M',time() ) ."\n\n";
	} else {
		$BookTitle = "Unknown book";
		$Body = "# Unknown book\n\n";
	}
	
	$j = 0;

	foreach($booknotes as $note){
		// Skip empty notes
		if(strlen($note['BroaderText']?$note['BroaderText']:$note['SelectedText'])==0) continue;
		
		$HighlightedText = str_replace("\n", "::\n::", $note['SelectedText']);
		
		// Keep some counters for commandline feedback
		if($j==0)$b++;
		$i++;
		$j++;
		
		// Put it in Evernote's ENEX format.
		$Body .= "::$HighlightedText::\n";
		$Body .= "> Date: " . @strftime('%d-%m-%Y %H%:%M',@strtotime("2001-01-01 +". ((int)$note['Created'])." seconds")) . "\n";
		if (!empty($note['Note'])) {
			$Body .= "> Note: " . $note['Note'] . "\n\n";
		}
		if (!empty($note['BroaderText']) && $note['BroaderText'] != $note['SelectedText']) {
			$Body .= "> Context: " . $note['BroaderText'] . "\n";
		}
		if (!empty($note['Chapter'])) {
			$Body .= "> Chapter: " . $note['Chapter'] . "\n";
		}

		$Body .= "\n---\n\n";
	}
	file_put_contents($BookTitle.".md", $Body);
}

echo "Done! Exported $i notes into $b separate export files in the '".RESULT_DIRECTORY_NAME."' folder.\n\n";
