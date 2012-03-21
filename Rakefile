require 'date'
require 'digest/md5'
require 'fileutils'
require 'nokogiri'

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

desc "Run PHPUnit tests (config in phpunit.xml)"
task :test do
  begin
    sh %{phpunit}
  rescue Exception
    exit 1
  end
end

desc "Create a PEAR package"
task :pear, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    Dir.chdir("library")

    now     = DateTime.now
    hash    = Digest::MD5.new
    xml     = Nokogiri::XML::Builder.new { |xml|
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

    Dir.chdir("..")
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Publish a PEAR package to pear.starzinger.net"
task :publish, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    package = "library/ImboClient-#{version}.tgz"

    if File.exists?(package)
      wd = Dir.getwd
      system "pirum add /home/christer/dev/christeredvartsen.github.com #{package}"
      Dir.chdir("/home/christer/dev/christeredvartsen.github.com")
      system "git add --all"
      system "git commit -a -m 'Added #{package[8..-5]}'"
      system "git push"
      Dir.chdir(wd)
      File.unlink(package)
    else
      puts "#{package} does not exist. Run the pear task first to create the package"
    end
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Tag current state of the master branch and push it to GitHub"
task :github, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    system "git checkout master"
    system "git merge develop"
    system "git tag #{version}"
    system "git push"
    system "git push --tags"
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end

desc "Publish API docs"
task :docs do
    system "git checkout master"
    system "docblox"
    wd = Dir.getwd
    Dir.chdir("/home/christer/dev/imboclient-php-ghpages")
    system "git pull origin gh-pages"
    system "cp -r #{wd}/build/docs/* ."
    system "git add --all"
    system "git commit -a -m 'Updated API docs [ci skip]'"
    system "git push origin gh-pages"
    Dir.chdir(wd)
end

desc "Release a new version (builds PEAR package, updates PEAR channel and pushes tag to GitHub)"
task :release, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    # Syntax check
    Rake::Task["lint"].invoke

    # Unit tests
    Rake::Task["test"].invoke

    # Build PEAR package
    Rake::Task["pear"].invoke(version)

    # Publish to the PEAR channel
    Rake::Task["publish"].invoke(version)

    # Tag the current state of master and push to GitHub
    Rake::Task["github"].invoke(version)

    # Update the API docs and push to gh-pages
    Rake::Task["docs"].invoke
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end
