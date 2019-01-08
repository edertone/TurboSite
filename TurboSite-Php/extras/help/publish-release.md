# How to make the library available to the public

1 - Commit and push all the new version changes to repository.

2 - Review git changelog to decide the new version value based on the GIT changes: minor, major, ...

3 - Make sure the git tag is updated with the new project version we want to publish

4 - Generate a release build executing tests (tb -crt)
	 - Make sure the phar is generated

5 - For now we are not publishing the library to composer, cause it requires the composer.json file to be on github root
	- so skip composer publishing

6 - Upload the lib to the respective site or server
	TODO