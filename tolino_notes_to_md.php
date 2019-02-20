<?php
/*
 *  Tolino notes to Markdown converter
 *  by Fabian Mürmann <hello@fabian.mu>
 * 
 *  based on https://github.com/jorisw/ibooks2evernote by Joris Witteman <joris@jor.is>
 *  
 *  Reads the notes.txt from your tolino  and exports
 *  them, tagged with their respective book title and imported in
 *  separate notebooks.
 *
 *  Usage:
 *  
 *  Move this script to the top of your personal home directory on your Mac.
 *  This is the folder that has your name, which the Finder opens if you
 *  click on the Finder icon in the Dock.
 *
 *  To export your notes to MD Files:
 *  
 *  1. Run the following command in the Terminal:
 *
 *     php ./tolino_notes_to_md.php
 *    
 *  2. Open the newly created "export"
 *
 */
 
// Default file locations for required iBooks data 
define('RESULT_DIRECTORY_NAME',"export");
define('NOTES_FILE','/Volumes/tolino/notes.txt');

// // Verify presence of notes file
if(!file_exists(NOTES_FILE)){
	die("Sorry, couldn't find the notes file, please attach your tolino\n");
}

$notes = explode("\n\n-----------------------------------\n\n",file_get_contents(NOTES_FILE));
if(count($notes)==0) die("No notes found in your library. Have you added any to iBooks?\n\nIf you did on other devices than this Mac, make sure to enable iBooks notes/bookmarks syncing on all devices.");

// Create a new directory and cd into it

@mkdir(RESULT_DIRECTORY_NAME);
chdir(RESULT_DIRECTORY_NAME);

$i=0;
$books = [];
foreach($notes as $note){
	$lines = explode("\n", $note);
	
	$title = array_shift($lines);
	$date = array_pop($lines);
	$highlight_note = implode("\n", $lines);

	$books[$title][] = [
		'title' => $title,
		'date' => $date,
		'highlight_note' => $highlight_note
	];
}

foreach ($books as $title => $notes) {
	$Body = "# $title\n\n";
	foreach ($notes as $note) {	
		// Keep some counters for commandline feedback
		$i++;

		$Body .= "## {$note['highlight_note']}\n";
		$Body .= "###### Date: {$note['date']}\n";
		// todo split into `Note on` and `Highlight on`

		$Body .= "\n---\n\n";
	}
	echo $Body;

	$filename = sanitize($title);

	file_put_contents($filename.".md", $Body);
}

echo "Done! Exported $i notes into " . count($books) . " separate export files in the '".RESULT_DIRECTORY_NAME."' folder.\n\n";

function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}