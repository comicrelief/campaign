#!/bin/bash
# @file
# Run test suite

# Check the config match with the info.yml
phing config:check
# Move to behat directory
cd $BEHAT_DIR
# behat.yml on the fly
{
  echo "#!/bin/bash"
  echo "cat <<EOF > behat.yml"
  cat "behat.yml.dist"
  echo "EOF"
} >> .behat.yml.sh
# Execute the script.
. .behat.yml.sh
# Run behat tests
./vendor/bin/behat --tags '~@not-on-travis'
