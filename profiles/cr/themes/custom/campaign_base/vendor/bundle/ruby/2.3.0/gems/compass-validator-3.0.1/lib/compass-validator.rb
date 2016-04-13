# This file was extracted from the blueprint project and then modified.
require "open3"

module Compass
  # Validates generated CSS against the W3 using Java
  class Validator
    VALIDATOR_DIR = File.join(File.dirname(__FILE__), 'java_validator')
    VALIDATOR_FILE = File.join(VALIDATOR_DIR, 'css-validator.jar')
    attr_reader :error_count
    attr_reader :css_directory
    
    def initialize(css_directory)
      @css_directory = css_directory
      @error_count = 0
      @bad_files = 0
      @files = 0
      @results = []
      @logger = Compass::Logger.new(:valid, :invalid)
      Compass::Logger::ACTION_COLORS[:valid] = :green
      Compass::Logger::ACTION_COLORS[:invalid] = :red
    end

    def self.execute(*directories)
      directories.each do |dir|
        new(dir).validate
      end
    end

    # Validates all three CSS files
    def validate
      java_path = `which java`.rstrip
      raise "You do not have a Java installed, but it is required." unless java_path && !java_path.empty?
    
      Dir.glob(File.join(css_directory, "**", "*.css")).each do |file_name|
        @files += 1
        if (count = validate_css_file(java_path, file_name))
          @error_count += count
          @bad_files += 1
          @logger.record(:invalid, file_name)
        else
          @logger.record(:valid, file_name)
        end
      end
    
      output_results
    end
    
    private
    def validate_css_file(java_path, css_file)
      jars = Dir.glob("#{VALIDATOR_DIR}/*.jar")
      cmd = "#{java_path} -classpath '#{jars.join(File::PATH_SEPARATOR)}' org.w3c.css.css.CssValidator -output text -profile css3 'file:#{css_file}'"
      Open3.popen3(cmd) do |stdin, stdout, stderr|
        result = stdout.read
        if result =~ /found the following errors \((\d+)\)/
          @results << [css_file, result]
          return $1.to_i
        end
      end
      nil
    end
    
    def output_results
      if @error_count == 0
        puts "\n\n"
        puts "************************************************************"
        puts 
        puts "Result: Valid"
        puts "#{@files} file#{"s"  if @files > 1 || @files == 0} validated."
        puts "So INTENSE!"
        puts 
        puts "************************************************************"
      else
        puts "\n\n"
        puts "************************************************************"
        puts 
        puts "Result: Invalid"
        puts "#{@files} file#{"s"  if @files > 1} validated."
        puts "#{@error_count} error#{"s" if @error_count > 1} found in #{@bad_files} file#{"s" if @bad_files > 1}."
        puts "Somewhere, a kitten is crying."
        puts 
        puts "************************************************************"
        @results.each do |file, result|
        puts "Output from: #{file}"
        puts result
        puts "************************************************************"
        end
      end
    end
  end
end