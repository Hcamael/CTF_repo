#!/usr/bin/env ruby
require 'openssl'
require 'timeout'

$stdout.sync = true
Dir.chdir(File.dirname(__FILE__))

class String
  def enhex
    self.unpack('H*')[0]
  end

  def dehex
    [self].pack('H*')
  end
end

flag = IO.read('flag')
KEY = IV = flag[/hitcon\{(.*)\}/, 1]
fail unless KEY.size == 16

def aes(s, mode)
  cipher = OpenSSL::Cipher::AES128.new(:CBC)
  cipher.send(mode)
  cipher.key = KEY
  cipher.iv = IV
  cipher.update(s) + cipher.final
end

def encrypt(s); aes(s, :encrypt) end
def decrypt(s); aes(s, :decrypt) end

m = 'The quick brown fox jumps over the lazy dog'
c = encrypt(m)
fail unless c.enhex == '4a5b8d0034e5469c071b60000ca134d9e04f07e4dcd6cf096b47ba48b357814e4a89ef1cfad33e1dd28b892ba7233285'
fail unless c.enhex.dehex == c
fail unless decrypt(c) == m

begin
  Timeout::timeout(30) do
    puts '1) Show me the source'
    puts "2) Let's decrypt"
    cmd = gets.to_i
    case cmd
    when 1
      puts IO.read(__FILE__)
    when 2
      c = gets.chomp.dehex
      m = decrypt(c)
      puts m.enhex
    else
      puts '...meow?'
    end
  end
rescue Timeout::Error
  puts 'Timeout ._./'
end