require 'date'
require 'digest/md5'
require 'fileutils'
require 'json'

basedir = "."
build   = "#{basedir}/build"
source  = "#{basedir}/src/ImboClient"
tests   = "#{basedir}/tests"

desc "Task used by Jenkins-CI"
task :jenkins => [:prepare, :lint, :installdep, :test, :apidocs, :phploc, :phpcs_ci, :phpcb, :phpcpd, :pdepend, :phpmd, :phpmd_html]

desc "Default task"
task :default => [:lint, :installdep, :test, :phpcs, :apidocs, :readthedocs]

desc "Spell check and generate end user docs"
task :readthedocs do
  wd = Dir.getwd
  Dir.chdir("docs")
  begin
    sh %{make spelling}
  rescue Exception
    puts "Spelling error in the docs, aborting"
    exit 1
  end
  puts "No spelling errors. Generate docs"
  sh %{make html}
  Dir.chdir(wd)
end

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
  Rake::Task["install_composer"].invoke
  system "php -d \"apc.enable_cli=0\" composer.phar install --dev"
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
  lintCache = "#{basedir}/.lintcache"

  begin
    sums = JSON.parse(IO.read(lintCache))
  rescue Exception => foo
    sums = {}
  end

  `git ls-files "*.php"`.split("\n").each do |f|
    f = File.absolute_path(f)
    md5 = Digest::MD5.hexdigest(File.read(f))

    next if sums[f] == md5

    sums[f] = md5

    begin
      sh %{php -l #{f}}
    rescue Exception
      exit 1
    end
  end

  IO.write(lintCache, JSON.dump(sums))
end

desc "Run unit tests"
task :test do
  begin
    sh %{./vendor/bin/phpunit --verbose -c tests --coverage-html build/coverage --coverage-clover build/logs/clover.xml --log-junit build/logs/junit.xml}
  rescue Exception
    exit 1
  end
end

desc "Run unit tests without code coverage"
task :test_no_cc do
  begin
    sh %{./vendor/bin/phpunit --verbose -c tests}
  rescue Exception
    exit 1
  end
end

desc "Generate API documentation using phpdoc"
task :apidocs do
  system "phpdoc -d #{source} -t #{build}/docs --title \"ImboClient API documentation\""
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

desc "Publish API docs"
task :publish_docs do
  system "git checkout master"

  # Geneate docs
  Rake::Task["apidocs"].invoke

  wd = Dir.getwd
  Dir.chdir("/home/christer/dev/imboclient-php-ghpages")
  system "git pull origin gh-pages"
  system "cp -r #{wd}/build/docs/* ."
  system "git add --all"
  system "git commit -am \"Updated API docs [ci skip]\""
  system "git push origin gh-pages"
  Dir.chdir(wd)
end

desc "Release a new version"
task :release, :version do |t, args|
  version = args[:version]

  if /^[\d]+\.[\d]+\.[\d]+$/ =~ version
    system "git checkout master"

    # Merge in changes from the develop branch
    system "git merge -m \"Merge branch 'develop'\" develop"

    # Set correct version
    system "sed -i \"s/const VERSION = '.*'/const VERSION = '#{version}'/\" src/ImboClient/Version.php"
    system "git commit -m \"Bumped version\" src/ImboClient/Version.php"

    system "git tag #{version}"
    system "git push"
    system "git push --tags"
    system "git checkout develop"

    Rake::Task["publish_docs"].invoke
  else
    puts "'#{version}' is not a valid version"
    exit 1
  end
end
