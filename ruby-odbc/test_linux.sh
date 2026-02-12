#! /bin/bash

LIBYAML_DEVEL_PACKAGE="libyaml-devel"

# 0. 필요한 패키지 설치
if [ ! $(yum list installed | grep $LIBYAML_DEVEL_PACKAGE) ]; then
    echo "Installing $LIBYAML_DEVEL_PACKAGE..."
    sudo yum install -y $LIBYAML_DEVEL_PACKAGE
else
    echo "$LIBYAML_DEVEL_PACKAGE is already installed."
fi

# 1. ruby 설치
if [ ! -d "$HOME/.rvm" ]; then
    echo "Installing Ruby..."
    curl -sSL https://get.rvm.io | bash -s stable
    rvm requirements run
    rvm install ruby-3.3.7
    echo "Ruby installed successfully."
else
    echo "Ruby is already installed."
fi

RUBY_VERSION=$(cut -d ' ' -f 2 < <(ruby -v))
echo "Ruby version: $RUBY_VERSION"
if [ "$RUBY_VERSION" != "3.3.7" ]; then
    echo "Ruby version is not 3.3.7. Using it."
    rvm use ruby-3.3.7
else
    echo "Ruby version is 3.3.7. Using it."
fi

GEM_INSTALLED_LIST=$(gem list)

REQUIRED_GEM_LIST="psych activerecord dbi inifile dbd-odbc ruby-odbc"

for gem in $REQUIRED_GEM_LIST; do
    if ! echo "$GEM_INSTALLED_LIST" | grep -q "$gem"; then
        echo "Installing $gem..."
        gem install $gem
    fi
done

ruby performance_tests.rb
