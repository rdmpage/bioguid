## Original PDFs ##

When generating PDFs with embedded metadata the code keeps the original PDF, these can take up considerable space. To delete these go to the cache folder and from the command line

```
find . -type f -name *.pdf_original -print0 | xargs -0 rm -f
```