#!/bin/bash

echo "Patching files"
# Directory where patches are stored on the site
PATCH_DIR="$PWD/patches"

# Check that the patch directory is a directory
if [ ! -d $PATCH_DIR ]; then
    echo "Invalid patch directory"
    exit 0
fi

# Check if the number of files ending in .patch is zero
if [ -z $( ls -A $PATCH_DIR|grep \\.patch) ]; then
    echo "No patches found"
    exit 0
fi

# Apply the patches
FAILED=false
FAILED_PATCHES=''
APPLIED_PATCHES=''
for PATCH in "$PATCH_DIR"/*.patch; do 
    echo "Applying patch $PATCH"

    if git apply -R --check "$PATCH" &> /dev/null; then
        # Patch has already been applied
        echo "Patch $PATCH has already been applied"
        continue
    fi
    
    # Patch has not yet been applied
    if git apply "$PATCH"; then
        echo "Applied patch $PATCH"
        APPLIED_PATCHES+=" $PATCH"
    else
        echo "Failed to apply patch $PATCH"
        FAILED=true
        FAILED_PATCHES+=" $PATCH"
    fi
done

if [ "$FAILED" = true ]; then
    # At least one patch failed
    echo "Failed to apply the following patches:$FAILED_PATCHES"
    exit 1;
fi

# Commit the changes
echo "Committing changes"
git config user.email "patches@gitlabci.jmaconsulting.biz"
git config user.name "patches-bot"
git remote add origin https://oauth2:$ACCESS_TOKEN@${CI_SERVER_HOST}/${CI_PROJECT_PATH}.git &> /dev/null # Hide errors if remote already exists
git add .
if git commit -m "Apply patches:$APPLIED_PATCHES"; then
    git push origin HEAD:$CI_COMMIT_REF_NAME -o ci.skip
    echo "Committed patched files to branch $CI_COMMIT_REF_NAME"
else
    echo "No files committed; all patches already applied"
fi
