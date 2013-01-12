require 'date'
require 'digest/md5'
require 'fileutils'
require 'nokogiri'

basedir = "."
build   = "#{basedir}/build"
source  = "#{basedir}/library/ImboClient"
tests   = "#{basedir}/tests"

desc "Task used by Jenkins-CI"
task :jenkins => [:prepare, :lint, :installdep, :test, :apidocs, :phploc, :phpcs_ci, :phpcb, :phpcpd, :pdepend, :phpmd, :phpmd_html]

desc "Task used by Travis-CI"
task :travis => [:installdep, :test]

desc "Default task"
task :default => [:lint, :installdep, :test, :phpcs, :apidocs]

desc "Clean up and create artifact directories"
task :prepare do
  FileUtils.rm_rf build
  FileUtils.mkdir build

  ["coverage", "logs", "docs", "code-browser", "pdepend"].each do |d|
    FileUtils.mkdir "#{build}/#{d}"
  end
end

desc "Install dependencies"
task :installdep do
  if ENV["TRAVIS"] == "true"
    system "composer --no-ansi install --dev"
  else
    Rake::Task["install_composer"].invoke
    system "php -d \"apc.enable_cli=0\" composer.phar install --dev"
  end
end

desc "Update dependencies"
task :updatedep do
  Rake::Task["install_composer"].invoke
  system "php -d \"apc.enable_cli=0\" composer.phar update --dev"
end

desc "Install/update composer itself"
task :install_composer do
  if File.exists?("composer.phar")
    system "php -d \"apc.enable_cli=0\" composer.phar self-update"
  else
    system "curl -s http://getcomposer.org/installer | php -d \"apc.enable_cli=0\""
  end
end

desc "Check syntax on all php files in the project"
task :lint do
  `git ls-files "*.php"`.split("\n").each do |f|
    begin
      sh %{php -l #{f}}
    rescue Exception
      exit 1
    end
  end
end

desc "Run unit tests"
task :test do
  if ENV["TRAVIS"] == "true"
    begin
      sh %{vendor/bin/phpunit --verbose -c phpunit.xml.travis}
    rescue Exception
      exit 1
    end
  else
    begin
      sh %{vendor/bin/phpunit --verbose}
    rescue Exception
      exit 1
    end
  end
end

desc "Generate API documentation using phpdoc"
task :apidocs do
  system "phpdoc -d #{tests} -d #{source} -t #{build}/docs --title \"ImboClient API documentation\""
end

desc "Generate phploc logs"
task :phploc do
  system "phploc --log-csv #{build}/logs/phploc.csv --log-xml #{build}/logs/phploc.xml #{source}"
end

desc "Generate checkstyle.xml using PHP_CodeSniffer"
task :phpcs_ci do
  system "phpcs --report=checkstyle --report-file=#{build}/logs/checkstyle.xml --standard=Imbo #{source}"
end

desc "Check CS"
task :phpcs do
  system "phpcs --standard=Imbo #{source}"
end

desc "Aggregate tool output with PHP_CodeBrowser"
task :phpcb do
  system "phpcb --source #{source} --output #{build}/code-browser"
end

desc "Generate pmd-cpd.xml using PHPCPD"
task :phpcpd do
  system "phpcpd --log-pmd #{build}/logs/pmd-cpd.xml #{source}"
end

desc "Generate jdepend.xml and software metrics charts using PHP_Depend"
task :pdepend do
  system "pdepend --jdepend-xml=#{build}/logs/jdepend.xml --jdepend-chart=#{build}/pdepend/dependencies.svg --overview-pyramid=#{build}/pdepend/overview-pyramid.svg #{source}"
end

desc "Generate pmd.xml using PHPMD (configuration in phpmd.xml)"
task :phpmd do
  system "phpmd #{source} xml #{basedir}/phpmd.xml --reportfile #{build}/logs/pmd.xml"
end

desc "Generate pmd.html using PHPMD (configuration in phpmd.xml)"
task :phpmd_html do
  system "phpmd #{source} html #{basedir}/phpmd.xml --reportfile #{build}/logs/pmd.html"
end

desc "Create a PEAR package"
task :generate_pear_package, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    Dir.chdir("library")

    now = DateTime.now
    hash = Digest::MD5.new
    xml = Nokogiri::XML::Builder.new { |xml|
      xml.package(:version => "2.0", :xmlns => "http://pear.php.net/dtd/package-2.0", "xmlns:tasks" => "http://pear.php.net/dtd/tasks-1.0", "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation" => ["http://pear.php.net/dtd/tasks-1.0", "http://pear.php.net/dtd/tasks-1.0.xsd", "http://pear.php.net/dtd/package-2.0", "http://pear.php.net/dtd/package-2.0.xsd"].join(" ")) {
        xml.name "ImboClient"
        xml.channel "pear.starzinger.net"
        xml.summary "Client for Imbo written in PHP"
        xml.description "This is a client to Imbo servers written in PHP. The client supports all operations possible on the server."
        xml.lead {
          xml.name "Christer Edvartsen"
          xml.user "christeredvartsen"
          xml.email "cogo@starzinger.net"
          xml.active "yes"
        }
        xml.developer {
          xml.name "Espen Hovlandsdal"
          xml.user "rexxars"
          xml.email "espen@hovlandsdal.com"
          xml.active "yes"
        }
        xml.date now.strftime('%Y-%m-%d')
        xml.time now.strftime('%H:%M:%S')
        xml.version {
          xml.release version
          xml.api version
        }
        xml.stability {
          xml.release "beta"
          xml.api "beta"
        }
        xml.license "MIT", :uri => "http://www.opensource.org/licenses/mit-license.php"
        xml.notes "http://github.com/imbo/imboclient-php/blob/#{version}/README.markdown"
        xml.contents {
          xml.dir(:name => "/") {
            `git ls-files`.split("\n").each { |f|
              xml.file(:md5sum => hash.hexdigest(File.read(f)), :role => "php", :name => f)
            }

            # Copy some files from the root directory
            ["README.markdown", "LICENSE", "ChangeLog.markdown"].each { |f|
              system "cp ../#{f} ."
              xml.file(:md5sum => hash.hexdigest(File.read(f)), :role => "doc", :name => f)
            }
          }
        }
        xml.dependencies {
          xml.required {
            xml.php {
              xml.min "5.3.2"
            }
            xml.pearinstaller {
              xml.min "1.9.0"
            }
            xml.extension {
              xml.name "spl"
            }
          }
        }
        xml.phprelease
      }
    }

    # Write XML to package.xml
    File.open("package.xml", "w") { |f|
      f.write(xml.to_xml)
    }

    # Generate pear package
    system "pear package"

    # Remove tmp files
    ["package.xml", "LICENSE", "README.markdown", "ChangeLog.markdown"].each { |f|
      File.unlink(f)
    }

    system "mv ImboClient-* .."

    Dir.chdir("..")
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Generate phar archive"
task :generate_phar_archive, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Path to stub
    stub = "#{basedir}/stub.php"

    # Generate stub
    File.open(stub, "w") do |f|
      f.write(<<-STUB)
<?php
/**
 * ImboClient
 *
 * Copyright (c) 2011-2013, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2013, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imboclient-php
 * @version #{version}
 */

Phar::mapPhar();

$basePath = 'phar://' . __FILE__;

spl_autoload_register(function($class) use ($basePath) {
    if (strpos($class, 'ImboClient\\\\') !== 0) {
        return false;
    }

    $file = $basePath . DIRECTORY_SEPARATOR . str_replace('\\\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require $file;
        return true;
    }

    return false;
});

__HALT_COMPILER();
STUB
    end

    # Generate the phar archive
    system "phar-build -s #{basedir}/library -S #{stub} --phar #{basedir}/imboclient.phar --ns --strip-files '.php$'"

    # Remove the stub
    File.unlink("stub.php")
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Publish a PEAR package to pear.starzinger.net"
task :publish_pear_package, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Generate PEAR package
    Rake::Task["generate_pear_package"].invoke(version)

    package = "ImboClient-#{version}.tgz"

    if File.exists?(package)
      wd = Dir.getwd
      Dir.chdir("/home/christer/dev/christeredvartsen.github.com")
      system "git pull origin master"
      system "pirum add . #{wd}/#{package}"
      system "git add --all"
      system "git commit -am 'Added #{package[0..-5]}'"
      system "git push"
      Dir.chdir(wd)
    else
      puts "#{package} does not exist. Run the pear task first to create the package"
    end
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Tag current state of the master branch and push it to GitHub"
task :tag_master_branch, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Checkout the master branch
    system "git checkout master"

    # Merge in the current state of the develop branch
    system "git merge develop"

    # Update phar arhive
    Rake::Task["generate_phar_archive"].invoke(version)
    system "git add imboclient.phar"
    system "git commit -m 'Updated phar archive' imboclient.phar"

    # Tag release and push
    system "git tag #{version}"
    system "git push"
    system "git push --tags"
    system "git checkout develop"
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Publish API docs"
task :publish_docs do
  system "git checkout master"

  Rake::Task["apidocs"].invoke

  wd = Dir.getwd
  Dir.chdir("/home/christer/dev/imboclient-php-ghpages")
  system "git pull origin gh-pages"
  system "cp -r #{wd}/build/docs/* ."
  system "git add --all"
  system "git commit -am 'Updated API docs [ci skip]'"
  system "git push origin gh-pages"
  Dir.chdir(wd)
end

desc "Release a new version"
task :release, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Publish to the PEAR channel
    Rake::Task["publish_pear_package"].invoke(version)

    # Tag the current state of master and push to GitHub
    Rake::Task["tag_master_branch"].invoke(version)

    # Update the API docs and push to gh-pages
    Rake::Task["publish_docs"].invoke
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end
