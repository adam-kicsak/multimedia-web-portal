copy NUL "%1\in.txt"
set FFMPEG_DATADIR=%cd%\[presets]

php phaseListener.php "%1" 1
ffmpeg -y -i "%1\%2" -acodec libvorbis -ac 2 -ab 96k -ar 44100 -threads 4 "%1\audio.ogg" >"%1\out.txt"  2>"%1\err.txt" <"%1\in.txt"

php phaseListener.php "%1" 2
ffmpeg -y -i "%1\%2" -acodec libmp3lame -ab 96k -ac 2 -ar 44100 "%1\audio.mp3" >"%1\out.txt" 2>"%1\err.txt" <"%1\in.txt"

php phaseListener.php "%1" 255
