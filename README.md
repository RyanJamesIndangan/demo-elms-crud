# Repository Name: demo-elms-crud
Smart Nonsense - Trial Task

# Tech Details
Database Collation: utf8_mb4_general_ci
Back-End Language, Version and Framework: CodeIgniter 3.1+ PHP 7.4+ (will not work properly on PHP 8+)
MySQL Version: 5.7+ (might have some problems with MySQL 8+)
API Platform: PostMan
Version Control system: Git
Code Repository Platform: GitHub

# How to run this code?
<!-- Local -->
1.) Composer Install.
2.) Cofigure the .env.
3.) Import the postman file for local.
4.) Import the SQL File.
5.) In your postman, configure the variables.
6.) Go to "API Auth" and trigger the "generate-token".
7.) Copy the token value and paste it in your variable token for initial and current value.
8.) You can check the miscellaneous but I decided not to implement it since it might take up some time to develop every .single validation and requirements for data insertion.
9.) Go to Question Folder and trigger the POST question.
10.) Enjoy!

<!-- Live -->
1.) Composer Install.
2.) Cofigure the .env.
3.) Import the postman file for live.
4.) In your postman, configure the variables.
5.) Go to "API Auth" and trigger the "generate-token".
6.) Copy the token value and paste it in your variable token for initial and current value.
7.) You can check the miscellaneous but I decided not to implement it since it might take up some time to develop every .single validation and requirements for data insertion.
8.) Go to Question Folder and trigger the POST question.
9.) Enjoy!

# Notes
1.) Make sure to have Composer, PHP 7.4+ and MySQL 5.7+ installed in your server.
2.) I copied your 404 page, please don't be mad hehe.
3.) I kind of deviated a bit to retain my coding style preferences, but definitely neat, easy to understand and implement.
4.) The database I designed and developed is highly scalable, meaning we can diverse from whatever spaghetti I build hahaha.
5.) Mostly I used TEXT so that you can try whatever, usually I use VARCHAR and limit it by 2s.. 2,4,8,16 and so on until 2048, cause if the column can be higher than that, I will just use TEXT instead
6.) Assuming there are images existing already, instead of uploading the images as attachment, since there's a comment in this link (https://cooing-bladder-b5b.notion.site/Sample-Question-Template-in-JSON-1582c74459628064b455eb89722bdae3) saying "This is just a sample if we want to add a image in a question, just put any public url for me access it and fetch it in the game" -Novi Rañoja
7.) Some endpoints doesn't validate the user_id (the thing that assumes someone is logged in) cause I hope I already demonstrated my capabilities, these things are just some fancy sprinkles for demo but very important in production, just to lessen my workload while doing this trial task hehe

# Future Updates When Hired 👀
1.) Robust Validations - For Example: Checks for same question, accepts only 1 correct answer, checks image url if existing and a lot lot more.
2.) Build them into a game or examination - since this is just for questions only, we can map it and hone it into a game (compilation of questions to make a game/exam/test/trial).
3.) Will follow the team's preferred coding convention - I prefer to use snake_case, but I will adapt to any coding standard that the team have.
4.) Solid audit trailing - notifications, account history etc and what not.
5.) Question Types - Like True or False, Essay/Situational, Needs auto correct or manual checking for the examiner/admin/moderator, or you know, can be AI to check if the essay is correct.
6.) Can get questions via categories, difficulties etc.
7.) Some calls me "10x Developer" some says "Unicorn Developer" but maybe I'm just some regular everyday developer, who knows? (Please Hire Me lmao 🙏🙏🙏, I work smarter and harder than most 🙇🙇🙇, I'm a fun guy, you don't want another stressful colleague don't you? Hehe 😆✌️).

# Work Tracker
Initial Work: December 30, 2024 at 6:58 PM until December 31, 2024 at 3:00 AM (9 hours total, but around 6~8 hours of work)

Second Time Working on this project:  December 31, 2024 at around 2:30 PM until  December 31, 2024 at around 4:40 PM

Total Working time on this project including database design and development: 11 hours (approximately 7~9 hours of work, the rest are compilation of short breaks) 