# ibooks2evernote
Export your iBooks notes to Evernote

Reads the iBooks Annotations library on your Mac and exports
them, tagged with their respective book title and imported in
separate notebooks.

Usage:

Move this script to the top of your personal home directory on your Mac.
This is the folder that has your name, which the Finder opens if you
click on the Finder icon in the Dock.

Before proceeding, make sure your Mac's iBooks has the books as well
as the highlights and notes that you're looking to export to Evernote.
This may require enabling iBooks Notes and Bookmarks Syncing on each
of your devices.

To export your notes to Evernote:

1. Run the following command in the Terminal:

   php ibooks2evernote.php
  
2. Open the newly created "iBooks exports for Evernote" folder from your
   home folder, open each file in there, Evernote will open and start 
   importing your notes.

Background: https://jor.is/en/blog/2015/04/22/ibooks-notes-evernote/
