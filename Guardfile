# A sample Guardfile
# More info at https://github.com/guard/guard#readme

guard 'phpunit', :cli => '--colors', :tests_path => 'tests/' do
  watch(%r{^.+Test\.php$})

  watch(%r{app/(.+)/(.+).php}) { |m| "tests/#{m[1]}/#{m[2]}Test.php" }

  watch(%r{^app/views/.+$}) { Dir.glob('tests/\**/*Test.php') }

end
