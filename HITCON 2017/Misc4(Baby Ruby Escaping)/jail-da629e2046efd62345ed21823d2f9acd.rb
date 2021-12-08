#!/usr/bin/env ruby

require 'readline'

proc {
  my_exit = Kernel.method(:exit!)
  my_puts = $stdout.method(:puts)
  ObjectSpace.each_object(Module) { |m| m.freeze if m != Readline }
  set_trace_func proc { |event, file, line, id, binding, klass|
    bad_id = /`|exec|foreach|fork|load|method_added|open|read(?!line$)|require|set_trace_func|spawn|syscall|system/
    bad_class = /(?<!True|False|Nil)Class|Module|Dir|File|ObjectSpace|Process|Thread/
    if event =~ /class/ || (event =~ /call/ && (id =~ bad_id || klass.to_s =~ bad_class))
      my_puts.call "\e[1;31m== Hacker Detected (#{$&}) ==\e[0m"
      my_exit.call
    end
  }
}.call

loop do
  line = Readline.readline('baby> ', true)
  puts '=> ' + eval(line, TOPLEVEL_BINDING).inspect
end
