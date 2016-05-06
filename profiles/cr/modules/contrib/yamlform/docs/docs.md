Steps for building and deploying documentation
----------------------------------------------

  1. Build docs
  2. Preview docs
  3. Clone GitHub pages (One-time)
  4. Deploy to GitHub pages
  5. Open docs


1. Build [MkDocs](http://www.mkdocs.org/)
-----------------------------------------

    # Build docs into /site directory
    mkdocs build --clean


2. Preview docs
---------------

    open http://localhost/d8_dev/modules/sandbox/yamlform/site;


3. Clone [GitHub pages](https://pages.github.com/) (One-time)
-------------------------------------------------------------

    git clone https://github.com/jrockowitz/yamlform.git gh-pages;
    cd gh-pages;
    git checkout gh-pages; 


4. Deploy to GitHub pages
-------------------------
    
    cd gh-pages;
    git rm *;
    cp -R ../site/* .;
    git add --all; 
    git commit -am"Deploying changes to GitHub"; 
    git push --set-upstream origin gh-pages;


5. Open docs
------------

    open http://thebigbluehouse.com/yamlform
