jeechan
=======

jeechan is a text-only 2channel style imageboard application, based off [Shiichan](http://wakaba.c3.cx/shii/shiichan) with support for a database backend and has been extensively modified to support new PHP versions. jeechan does not require users to register for an account. 

Installation guide
------------

1. Copy /includes/settings.default.php to /includes/settings.php
2. Create a database and import /includes/install.sql into it
3. Edit settings.php with the database connection credentials
4. Browse to /admin.php
    * The default username and password is admin / admin


Original copyright
------------

Shiichan  
Copyright (C) 2004-2009 Shii  
Portions copyright (C) 2004-2009 Alexander Strange (MrVacBob)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

(Below is an excerpt from the Shiichan official project page)

What's "2channel style"?
------------

A 2channel style board is a sort of anonymous bulletin board system. You can have as many forums as you want; the latest posts in each thread are previewed in the front page of each forum. Nicknames are optional. A system is implemented to allow "registration"-like features without storing any user information in a database.
This type of board is based off of 2channel, pronounced "Ni-channeru", the largest Internet forum in the world (20 times larger than the biggest American forum). Shiichan is somewhat different from the 2ch look, but they still use the same system. Kareha is very closely modelled on 2ch.

Why is this better than regular forum software?
------------

If you want to create a beautiful "community", forum software is not for you. You should rather find some way to securely verify people's identities and then talk with them on a first-name basis. Once you start allowing pseudonyms, anything goes.
On the other hand, you're interested in starting a forum on some topic of your interest, and allowing anyone to post, then 2ch-type is infinitely better than PhpBB, Invision, or vBulletin. I'm going to refer to these as "old-type forum software"; I'm not pretending to be unbiased.

Here's why:

1. Registration keeps out good posters. Imagine someone with an involving job related to your forum comes across it. This person is an expert in her field, and therefore would be a great source of knowledge for your forum; but if a registration, complete with e-mail and password, is necessary before posting, she might just give up on posting and do something more important. People with lives will tend to ignore forums with a registration process.
2. Registration lets in bad posters. On the other hand, people with no lives will thrive on your forum. Children and Internet addicts tend to have free time to go register an account and check their e-mail for the confirmation message. They will generally make your forum a waste of bandwidth.
Registration attracts trolls. If someone is interested in destroying a forum, a registration process only adds to the excitement of a challenge. One might argue that a lack of registration will just let "anyone" post, but in reality anyone can post on old-type forum software; registration is merely a useless hassle. Quoting a 4channeler:
    * Trolls are not out to protect their own reputation. They seek to destroy other peoples' "reputation" ... Fora with only registered accounts are like a garden full of flowers of vanity a troll would just love to pick.
3. Anonymity counters vanity. On a forum where registration is required, or even where people give themselves names, a clique is developed of the elite users, and posts deal as much with who you are as what you are posting. On an anonymous forum, if you can't tell who posts what, logic will overrule vanity. As Hiroyuki, the administrator of 2ch, writes:
    * If there is a user ID attached to a user, a discussion tends to become a criticizing game. On the other hand, under the anonymous system, even though your opinion/information is criticized, you don't know with whom to be upset. Also with a user ID, those who participate in the site for a long time tend to have authority, and it becomes difficult for a user to disagree with them. Under a perfectly anonymous system, you can say, "it's boring," if it is actually boring. All information is treated equally; only an accurate argument will work.

This is hard to believe. (2006)
------------

Problems with 2ch-type forums often come along the lines of "people will be more likely to insult, flame, and troll if they're anonymous". This may be true... but people are already pseudonymous on most forums. The drama and hatred you see on pseudonymous forums is as bad as it gets; with anonymity, you'll probably be better off because of the convenience. Either way you will need a dedicated team of moderators to police the board for trolling and nonsense.

A preliminary study done by... me in March 2005 found that there was no noticeable difference between 2channel and forums.gentoo.org in terms of useful posts, off-topic posts, and nonsense in a long thread about technical issues. On the American forum 4-ch.net where posts can be either anonymous or pseudonymous, most of the actual helpful contributions to technical discussions came from anonymous users, whereas pseudonymous users tended to offer their personal experiences. But this was totally unscientific. Do a blind study yourself.

Spam is another issue. Since 2004 when this essay was written, message board spam has become increasingly prevalent on all anonymous forums. However, on old-style forums spammers often register fake accounts and happily suck in users to their profile websites without posting. If you are experiencing spam that gets around your local filters, I have found that extremely simple tests, such as a drop-down box asking whether you are a human (Yes? No? Maybe?) often cut it off entirely.

If you can't or don't want to force people to pay or use their real names, at least give a swing at bucking the establishment and trying out a totally anonymous forum.


