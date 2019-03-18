This was a thing to generate avatars from large image libraries (for dummy site data) but I've altered it to make neat machine learning data!

It takes images and turns them into little squares, and outputs each file as a JSON input matrix alongside its index.

You'll need
- php version >= 5
- gd `sudo apt-get install php5-gd`

Running with arguments (these take priority):
`php run.php <source_image_directory> <target_directory> <image_size>`

else just run
`php run.php` 
and edit all of the configurations in the `config.php` file.
