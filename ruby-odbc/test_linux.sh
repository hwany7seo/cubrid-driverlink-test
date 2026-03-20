#!/bin/bash
# performance_tests.rb — dbi, dbd-odbc, ruby-odbc, inifile (psych는 Ruby 빌트인, gem 설치 대상 아님)
cd "$(dirname "$0")"

RUBY_VER="ruby-3.3.7"

install_ruby_build_deps() {
    local pkgs=(libyaml-devel openssl-devel readline-devel zlib-devel gdbm-devel ncurses-devel gcc make patch autoconf automake)
    local need=()
    for p in "${pkgs[@]}"; do
        rpm -q "$p" &>/dev/null || need+=("$p")
    done
    if ((${#need[@]})); then
        echo "Installing Ruby build dependencies: ${need[*]}"
        sudo yum install -y "${need[@]}"
    fi
}

import_rvm_gpg() {
    command -v gpg2 &>/dev/null || return 0
    gpg2 --keyserver hkp://keyserver.ubuntu.com --recv-keys \
        409B6B1796C275462A1703113804BB82D39DC0E3 \
        7D2BAF1CF37B13E2069D6956105BD0E739499BDB 2>/dev/null || true
}

source_rvm() {
    if [[ -s "$HOME/.rvm/scripts/rvm" ]]; then
        # shellcheck source=/dev/null
        source "$HOME/.rvm/scripts/rvm"
        return 0
    fi
    if [[ -s "/usr/local/rvm/scripts/rvm" ]]; then
        # shellcheck source=/dev/null
        source "/usr/local/rvm/scripts/rvm"
        return 0
    fi
    return 1
}

install_ruby_build_deps
import_rvm_gpg

if ! source_rvm; then
    echo "Installing RVM..."
    curl -sSL https://get.rvm.io | bash -s stable
    source_rvm || { echo "ERROR: source RVM: ~/.rvm/scripts/rvm or /usr/local/rvm/scripts/rvm"; exit 1; }
fi

if ! rvm list strings | grep -qx "$RUBY_VER"; then
    echo "Installing $RUBY_VER..."
    rvm requirements run
    rvm install "$RUBY_VER"
fi

rvm use "$RUBY_VER" --default 2>/dev/null || rvm use "$RUBY_VER"

if ! ruby -rpsych -e 'puts :ok' &>/dev/null; then
    echo "Reinstalling $RUBY_VER so psych (libyaml) is linked..."
    rvm reinstall "$RUBY_VER"
    rvm use "$RUBY_VER"
fi

if ! ruby -rpsych -e0 &>/dev/null; then
    echo "ERROR: psych missing. Run: sudo yum install -y libyaml-devel && rvm reinstall $RUBY_VER"
    exit 1
fi

GEM_INSTALLED_LIST=$(gem list)
# performance_tests.rb: dbi, dbd-odbc, ruby-odbc, inifile — activerecord/psych 제거
REQUIRED_GEMS=(dbi inifile dbd-odbc ruby-odbc)
for g in "${REQUIRED_GEMS[@]}"; do
    if ! echo "$GEM_INSTALLED_LIST" | grep -q "^${g} "; then
        echo "Installing gem $g..."
        gem install "$g" || exit 1
    fi
done

ruby performance_tests.rb
