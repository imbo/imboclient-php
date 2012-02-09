require 'rubygems'
require 'date'
require 'digest/md5'
require 'fileutils'
require 'nokogiri'

desc "Check syntax on all php files in the project"
task :lint do
    `git ls-files "*.php"`.each do |f|
        begin
            sh %{php -l #{f}}
        rescue Exception
            exit 1
        end
    end
end

desc "Create a PEAR package"
task :pear, :version do |t, args|
    version = args[:version]

    if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
        Dir.chdir('library')

        now     = DateTime.now
        hash    = Digest::MD5.new
        xml     = Nokogiri::XML::Builder.new do |xml|
            xml.package(
                :version => "2.0",
                :xmlns => "http://pear.php.net/dtd/package-2.0",
                "xmlns:tasks" => "http://pear.php.net/dtd/tasks-1.0",
                "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
                "xsi:schemaLocation" => ["http://pear.php.net/dtd/tasks-1.0", "http://pear.php.net/dtd/tasks-1.0.xsd", "http://pear.php.net/dtd/package-2.0", "http://pear.php.net/dtd/package-2.0.xsd"].join(" ")
            ) {
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
                xml.contributor {
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
                xml.notes "http://github.com/christeredvartsen/imboclient-php/blob/#{version}/README.markdown"
                xml.contents {
                    xml.dir(:name => "/") {
                        system "cp ../README.markdown ../LICENSE ."

                        `git ls-files`.split("\n").each { |f|
                            xml.file(:md5sum => hash.hexdigest(File.read(f)), :role => "php", :name => f)
                        }

                        ["README.markdown", "LICENSE"].each { |f|
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
        end

        # Write XML to package.xml
        File.open("package.xml", "w") { |f|
            f.write(xml.to_xml)
        }

        # Generate pear package
        system "pear package"

        # Remove tmp files
        ["package.xml", "LICENSE", "README.markdown"].each { |f|
            File.unlink(f)
        }

        Dir.chdir("..")
    end
end

desc "Publish a PEAR package to pear.starzinger.net"
task :publish, :version do |t, args|
    version = args[:version]

    if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
        system "scp library/ImboClient-#{version}.tgz pear.starzinger.net:~"
        system "ssh pear.starzinger.net 'pirum add /services/apache/pear.starzinger.net/html ImboClient-#{version}.tgz'"
        File.unlink("library/ImboClient-#{version}.tgz")
    end
end

desc "Tag current state of the master branch and push it to GitHub"
task :github, :version do |t, args|
    version = args[:version]

    if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
        system "git checkout master"
        system "git tag #{version}"
        system "git push --tags"
    end
end

desc "Release a new version (builds PEAR package, updates PEAR channel and pushes tag to GitHub)"
task :release, :version do |t, args|
    version = args[:version]

    if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
        # Syntax check
        Rake::Task("lint")

        # Build PEAR package
        Rake::Task("pear").invoke(version)

        # Publish to the PEAR channel
        Rake::Task("publish").invoke(version)

        # Tag the current state of master and push to GitHub
        Rake::Task("github").invoke(version)
    end
end
