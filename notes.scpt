FasdUAS 1.101.10   ��   ��    k             i         I     �� 	��
�� .aevtoappnull  �   � **** 	 o      ���� 0 argv  ��    k     � 
 
     l     ��������  ��  ��        Q     �     k    n       l   ��  ��    ! 	set book to item 1 of argv     �   6 	 s e t   b o o k   t o   i t e m   1   o f   a r g v      l   ��  ��    ( "	set noteContent to item 2 of argv     �   D 	 s e t   n o t e C o n t e n t   t o   i t e m   2   o f   a r g v      l   ��   ��    &   set titlefile to item 1 of argv      � ! ! @   s e t   t i t l e f i l e   t o   i t e m   1   o f   a r g v   " # " r    	 $ % $ n     & ' & 4    �� (
�� 
cobj ( m    ����  ' o    ���� 0 argv   % o      ���� 0 notefile   #  ) * ) l  
 
��������  ��  ��   *  + , + l  
 
�� - .��   -  set listOfShows to {}    . � / / * s e t   l i s t O f S h o w s   t o   { } ,  0 1 0 l  
 
�� 2 3��   2 ; 5set Shows to paragraphs of (read POSIX file notefile)    3 � 4 4 j s e t   S h o w s   t o   p a r a g r a p h s   o f   ( r e a d   P O S I X   f i l e   n o t e f i l e ) 1  5 6 5 l  
 
�� 7 8��   7 # repeat with nextLine in Shows    8 � 9 9 : r e p e a t   w i t h   n e x t L i n e   i n   S h o w s 6  : ; : l  
 
�� < =��   < 3 -	if length of nextLine is greater than 0 then    = � > > Z 	 i f   l e n g t h   o f   n e x t L i n e   i s   g r e a t e r   t h a n   0   t h e n ;  ? @ ? l  
 
�� A B��   A / )		copy nextLine to the end of listOfShows    B � C C R 	 	 c o p y   n e x t L i n e   t o   t h e   e n d   o f   l i s t O f S h o w s @  D E D l  
 
�� F G��   F  	end if    G � H H  	 e n d   i f E  I J I l  
 
�� K L��   K  
end repeat    L � M M  e n d   r e p e a t J  N O N l  
 
��������  ��  ��   O  P Q P l  
 
��������  ��  ��   Q  R S R l  
 
��������  ��  ��   S  T U T l  
 
�� V W��   V  on readFile( notefile )    W � X X . o n   r e a d F i l e (   n o t e f i l e   ) U  Y Z Y l  
 
��������  ��  ��   Z  [ \ [ l  
 
�� ] ^��   ] 9 3set foo to (open for access (POSIX file titlefile))    ^ � _ _ f s e t   f o o   t o   ( o p e n   f o r   a c c e s s   ( P O S I X   f i l e   t i t l e f i l e ) ) \  ` a ` l  
 
�� b c��   b J Dset titlefileContent to (read foo for (get eof foo) as �class utf8�)    c � d d � s e t   t i t l e f i l e C o n t e n t   t o   ( r e a d   f o o   f o r   ( g e t   e o f   f o o )   a s   � c l a s s   u t f 8 � ) a  e f e l  
 
�� g h��   g  close access foo    h � i i   c l o s e   a c c e s s   f o o f  j k j l  
 
��������  ��  ��   k  l m l r   
  n o n l  
  p���� p I  
 �� q��
�� .rdwropenshor       file q l  
  r���� r 4   
 �� s
�� 
psxf s o    ���� 0 notefile  ��  ��  ��  ��  ��   o o      ���� 0 foo   m  t u t r    $ v w v l   " x���� x I   "�� y z
�� .rdwrread****        **** y o    ���� 0 foo   z �� { |
�� 
rdfr { l    }���� } I   �� ~��
�� .rdwrgeofcomp       **** ~ o    ���� 0 foo  ��  ��  ��   | �� ��
�� 
as    m    ��
�� 
utf8��  ��  ��   w o      ���� "0 notefilecontent notefileContent u  � � � l  % %�� � ���   � . (my logit(notefileContent, "TestDrive")		    � � � � P m y   l o g i t ( n o t e f i l e C o n t e n t ,   " T e s t D r i v e " ) 	 	 �  � � � I  % *�� ���
�� .rdwrclosnull���     **** � o   % &���� 0 foo  ��   �  � � � l  + +��������  ��  ��   �  � � � l  + +�� � ���   �  
return txt    � � � �  r e t u r n   t x t �  � � � l  + +��������  ��  ��   �  � � � l  + +�� � ���   �  end readFile    � � � �  e n d   r e a d F i l e �  � � � l  + +��������  ��  ��   �  � � � l  + +��������  ��  ��   �  � � � l  + +��������  ��  ��   �  � � � O   + l � � � O   / k � � � Z   6 j � ����� � I  6 B�� ���
�� .coredoexnull���     **** � 4   6 >�� �
�� 
cfol � m   : = � � � � �  r e a d : h i g h l i g h t s��   � k   E f � �  � � � l  E E�� � ���   � a [				make new note at folder "read:highlights" with properties {name:book, body:noteContent}    � � � � � 	 	 	 	 m a k e   n e w   n o t e   a t   f o l d e r   " r e a d : h i g h l i g h t s "   w i t h   p r o p e r t i e s   { n a m e : b o o k ,   b o d y : n o t e C o n t e n t } �  � � � l  E E�� � ���   � m gmake new note at folder "read:highlights" with properties {name:titlefileContent, body:notefileContent}    � � � � � m a k e   n e w   n o t e   a t   f o l d e r   " r e a d : h i g h l i g h t s "   w i t h   p r o p e r t i e s   { n a m e : t i t l e f i l e C o n t e n t ,   b o d y : n o t e f i l e C o n t e n t } �  ��� � I  E f���� �
�� .corecrel****      � null��   � �� � �
�� 
kocl � m   I L��
�� 
note � �� � �
�� 
insh � 4   O W�� �
�� 
cfol � m   S V � � � � �  r e a d : h i g h l i g h t s � �� ���
�� 
prdt � K   Z ` � � �� ���
�� 
body � o   ] ^���� "0 notefilecontent notefileContent��  ��  ��  ��  ��   � 4   / 3�� �
�� 
acct � m   1 2 � � � � �  i C l o u d � m   + , � �z                                                                                      @ alis      root                           BD ����	Notes.app                                                      ����            ����  
 cu             Applications   /:System:Applications:Notes.app/   	 N o t e s . a p p  
  r o o t  System/Applications/Notes.app   / ��   �  ��� � l  m m��������  ��  ��  ��    R      �� � �
�� .ascrerr ****      � **** � o      ���� 0 e   � �� ���
�� 
errn � o      ���� 0 n  ��    n  v � � � � I   w ��� ����� 	0 logit   �  � � � b   w � � � � b   w � � � � b   w | � � � m   w z � � � � �  O O P s :   � o   z {���� 0 e   � m   |  � � � � �    � o   � ����� 0 n   �  ��� � m   � � � � � � �  T e s t D r i v e��  ��   �  f   v w   ��� � l  � ���������  ��  ��  ��     � � � l     ��������  ��  ��   �  � � � l     ��~�}�  �~  �}   �  ��| � i    � � � I      �{ ��z�{ 	0 logit   �  � � � o      �y�y 0 
log_string   �  ��x � o      �w�w 0 log_file  �x  �z   � k      � �  � � � l     �v � ��v   �  display dialog log_string    � � � � 2 d i s p l a y   d i a l o g   l o g _ s t r i n g �  ��u � I    �t ��s
�t .sysoexecTEXT���     TEXT � b     	 � � � b      � � � b      � � � b      � � � l 	    ��r�q � m      � � � � � : e c h o   ` d a t e   ' + % Y - % m - % d   % T :   ' ` "�r  �q   � o    �p�p 0 
log_string   � l 	   ��o�n � m     � � � � � 0 "   > >   $ H O M E / L i b r a r y / L o g s /�o  �n   � o    �m�m 0 log_file   � m     � � � � �  . l o g�s  �u  �|       �l � � ��l   � �k�j
�k .aevtoappnull  �   � ****�j 	0 logit   � �i �h�g �f
�i .aevtoappnull  �   � ****�h 0 argv  �g    �e�d�c�e 0 argv  �d 0 e  �c 0 n   !�b�a�`�_�^�]�\�[�Z�Y�X�W�V ��U ��T ��S�R�Q�P ��O�N�M�L�K � � ��J
�b 
cobj�a 0 notefile  
�` 
psxf
�_ .rdwropenshor       file�^ 0 foo  
�] 
rdfr
�\ .rdwrgeofcomp       ****
�[ 
as  
�Z 
utf8�Y 
�X .rdwrread****        ****�W "0 notefilecontent notefileContent
�V .rdwrclosnull���     ****
�U 
acct
�T 
cfol
�S .coredoexnull���     ****
�R 
kocl
�Q 
note
�P 
insh
�O 
prdt
�N 
body�M 
�L .corecrel****      � null�K 0 e   �I�H�G
�I 
errn�H 0 n  �G  �J 	0 logit  �f � p��k/E�O*��/j E�O���j ��� 
E�O�j O� >*��/ 6*a a /j  &*a a a *a a /a a �la  Y hUUOPW X  )a �%a %�%a l+  OP � �F ��E�D�C�F 	0 logit  �E �B�B   �A�@�A 0 
log_string  �@ 0 log_file  �D   �?�>�? 0 
log_string  �> 0 log_file    � � ��=
�= .sysoexecTEXT���     TEXT�C �%�%�%�%j ascr  ��ޭ