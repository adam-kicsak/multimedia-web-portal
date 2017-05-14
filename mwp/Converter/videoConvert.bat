copy NUL "%1\in.txt"

php phaseListener.php "%1" 1
ffmpeg -y -i "%1\%2" -acodec libvorbis -ac 2 -ab 96k -ar 44100 -vcodec libvpx -b 400k -s "%3" -threads 4 "%1\video.webm" >"%1\out.txt"  2>"%1\err.txt" <"%1\in.txt"

php phaseListener.php "%1" 2
ffmpeg -y -i "%1\%2" -acodec aac -ac 2 -ab 96k -ar 44100 -vcodec libx264 -level 21 -refs 2 -b 400k -bt 400k -s "%3" -threads 0 "%1\video.mp4" >"%1\out.txt" 2>"%1\err.txt" <"%1\in.txt"

php phaseListener.php "%1" 3
ffmpeg -y -i "%1\%2" -r 1 -f image2 -vframes 1 -s "%4" -ss "%5" "%1\poster.jpeg" >"%1\out.txt"  2>"%1\err.txt" <"%1\in.txt"

php phaseListener.php "%1" 255