<?php
/*
 *  Apple Books (iBooks) notes to Bear.app
 *  by Fabian Mürmann <hello@fabian.mu>
 * 
 *  based on https://github.com/jorisw/ibooks2evernote by Joris Witteman <joris@jor.is>
 *  
 *  Reads the Books Annotations library on your Mac and exports
 *  them, tagged with their respective book title and imported in
 *  separate notes.
 * 
 *  Contains binary built from https://github.com/martinfinke/xcall/pull/6 to handle bear.app bear://x-callback-url URLS
 *
 *  Usage:
 * 
 *  To export your notes to MD Files:
 *  
 *  1. Run the following command in the Terminal:
 *
 *     php ./book_notes_to_bear.php YOUR-BEAR-API-TOKEN
 *  
 * 
 */

// Default file locations for required iBooks data 
define('BOOKS_DATABASE_DIRECTORY', '~/Library/Containers/com.apple.iBooksX/Data/Documents/BKLibrary');
define('NOTES_DATABASE_DIRECTORY', '~/Library/Containers/com.apple.iBooksX/Data/Documents/AEAnnotation');

// Verify presence of iBooks database

$path = exec('ls ' . BOOKS_DATABASE_DIRECTORY . "/*.sqlite");
define('BOOKS_DATABASE_FILE', $path);
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
$path = exec('ls ' . NOTES_DATABASE_DIRECTORY . "/*.sqlite");
define('NOTES_DATABASE_FILE', $path);
// if(!file_exists(NOTES_DATABASE_DIRECTORY)){
// 	die("Sorry, couldn't find any iBooks notes on your Mac. Have you actually taken any notes in iBooks?\n");
// }else{
// 	if(!$path = exec('ls '.NOTES_DATABASE_DIRECTORY."/*.sqlite")){
// 		die("Could not find the iBooks notes database. Have you actually taken any notes in iBooks?\n");
// 	}else{
// 		define('NOTES_DATABASE_FILE',$path);
// 	}
// }

var_dump($argv);
if (!isset($argv[1])) {
    die("You must specify your Bear.app API Token as argument");
}
define('BEAR_APP_TOKEN', $argv[1]);

// Fire up a SQLite parser
class MyDB extends SQLite3
{
    function __construct($FileName)
    {
        $this->open($FileName);
    }
}

function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function getNoteContent($title, $identifier)
{
    $sluggedIdentifier = slugify("notecontent-$title");
    exec('xcall.app/Contents/MacOS/xcall -url "bear://x-callback-url/open-note?id=' . $identifier . '" > runfiles/' . $sluggedIdentifier, $output);
    $noteContent = file_get_contents("runfiles/$sluggedIdentifier");
    return json_decode($noteContent)->note;
}

function createNote($title, $slug, $content)
{
    $sluggedIdentifier = slugify(substr($content, 0, 200));
    exec('xcall.app/Contents/MacOS/xcall -url "bear://x-callback-url/create?title=Book%3A%20' . rawurlencode($title) . '&text=' . rawurlencode($content) . '" > runfiles/' . $sluggedIdentifier, $output);
    $noteContent = file_get_contents("runfiles/$sluggedIdentifier");
    sleep(1);
    return json_decode($noteContent);
}

function addNote($identifier, $annotation)
{
    $sluggedIdentifier = slugify(substr($annotation, 0, 200));
    exec('xcall.app/Contents/MacOS/xcall -url "bear://x-callback-url/add-text?id=' . $identifier . '&mode=append&text=' . rawurlencode($annotation) . '" > runfiles/' . $sluggedIdentifier, $output);
}

function searchBook($title)
{
    $sluggedIdentifier = slugify($title);
    exec('xcall.app/Contents/MacOS/xcall -url "bear://x-callback-url/search?term=Book%3A%20' . rawurlencode($title) . '&show_window=no&token=' . BEAR_APP_TOKEN . '" > runfiles/' . $sluggedIdentifier, $output, $return_var);
    $searchResult = file_get_contents("runfiles/$sluggedIdentifier");
    $searchResult = json_decode($searchResult);
    $bearBooks = json_decode($searchResult->notes);
    if (count($bearBooks) == 0) {
        return false;
    } else {
        return $bearBooks[0];
    }
}

// Retrieve any books.

$books = array();
$booksdb = new MyDB(BOOKS_DATABASE_FILE);
@mkdir("runfiles");
if (!$booksdb) {
    echo $booksdb->lastErrorMsg();
}

$res = $booksdb->query("
			SELECT
				ZASSETID,
				ZTITLE AS Title,
				ZAUTHOR AS Author
			FROM ZBKLIBRARYASSET
			WHERE ZTITLE IS NOT NULL");

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $books[$row['ZASSETID']] = $row;
}

$booksdb->close();

if (count($books) == 0) die("No books found in your library. Have you added any to iBooks?\n");

// Retrieve the notes.

$notesdb = new MyDB(NOTES_DATABASE_FILE);

if (!$notesdb) {
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

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $notes[$row['ZANNOTATIONASSETID']][] = $row;
}

$notesdb->close();


if (count($notes) == 0) die("No notes found in your library. Have you added any to iBooks?\n\nIf you did on other devices than this Mac, make sure to enable iBooks notes/bookmarks syncing on all devices.");
0;

foreach ($notes as $AssetID => $booknotes) {
    if (array_key_exists($AssetID, $books)) {
        $bookTitle = $books[$AssetID]['Title'];
        $bookAuthor = $books[$AssetID]['Author'];
    } else {
        $bookTitle = "Unknown book";
        $bookAuthor = "Unknown";
    }

    $slug = slugify($bookTitle);

    $header = "by $bookAuthor Export date: " . @strftime('%d-%m-%Y %H%:%M', time()) . "\n\n";
    $header .= "#book/" . $slug . "\n\n";

    $bearBook = searchBook($bookTitle);
    $bearBookContent = "";
    if ($bearBook) {
        echo $bookTitle . " exists, adding\n";
        $bearBookContent = getNoteContent($bookTitle, $bearBook->identifier);
    } else {
        echo $bookTitle . " is new\n";
    }
    // if ($bookTitle != 'Why We Sleep') {
    //     echo "skipping";
    //     continue;
    // }

    foreach ($booknotes as $note) {
        // Skip empty notes
        if (strlen($note['BroaderText'] ? $note['BroaderText'] : $note['SelectedText']) == 0) continue;

        $highlightedText = $note['SelectedText'];
        $annotation = "";

        // leverage Bear.app's ::text:: Syntax for marked text
        if (!empty($note['BroaderText']) && $note['BroaderText'] != $note['SelectedText']) {
            $annotation .= str_replace(str_replace("\n", "", $note['SelectedText']), "::{$note['SelectedText']}::", $note['BroaderText']) . "\n";
        } else if (!empty($note['BroaderText']) && $note['BroaderText'] == $note['SelectedText']) {
            $annotation .= "::$highlightedText::\n";
            $annotation .= "Context: " . $note['BroaderText'] . "\n";
        } else {
            $annotation .= "::$highlightedText::\n";
        }

        $annotation .= "Date: " . @strftime('%d-%m-%Y %H%:%M', @strtotime("2001-01-01 +" . ((int) $note['Created']) . " seconds")) . "\n";
        if (!empty($note['Note'])) {
            $annotation .= "> Note: " . $note['Note'] . "\n\n";
        }
        if (!empty($note['Chapter'])) {
            $annotation .= "Chapter: " . $note['Chapter'] . "\n";
        }

        if (!$bearBook) {
            $bearBook = createNote($bookTitle, $slug, $header);
            $bearBookContent = getNoteContent($bookTitle, $bearBook->identifier);
        }

        if (strpos($bearBookContent, $annotation) === false) {
            addNote($bearBook->identifier, $annotation . "\n---\n\n");
        }
    }
}
