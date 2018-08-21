# How to make the library available to the public

1 - Make sure the git tag is updated with the new project version we want to publish

2 - Generate a release build executing tests (tb -crt)
	 - Make sure the phar is generated

3 - For now we are not publishing the library to composer, cause it requires the composer.json file to be on github root
	- so skip composer publishing

4 - Upload the lib to the respective site or server
	TODO