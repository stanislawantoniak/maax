require 'spec_helper_system'

describe 'mysql_grant' do

  describe 'setup' do
    it 'setup mysql::server' do
      pp = <<-EOS
        class { 'mysql::server': }
      EOS

      puppet_apply(pp)
    end
  end

  describe 'missing privileges for user' do
    it 'should fail' do
      pp = <<-EOS
        mysql_grant { 'test1@tester/test.*':
          ensure => 'present',
          table  => 'test.*',
          user   => 'test1@tester',
        }
      EOS

      puppet_apply(pp) do |r|
        r.stderr.should =~ /privileges parameter is required/
      end
    end

    it 'should not find the user' do
      shell("mysql -NBe \"SHOW GRANTS FOR test1@tester\"") do |r|
        r.stderr.should =~ /There is no such grant defined for user 'test1' on host 'tester'/
        r.exit_code.should eq 1
      end
    end
  end

  describe 'missing table for user' do
    it 'should fail' do
      pp = <<-EOS
        mysql_grant { 'atest@tester/test.*':
          ensure => 'present',
          user   => 'atest@tester',
          privileges => ['ALL'],
        }
      EOS

      puppet_apply(pp) do |r|
        r.exit_code.should eq 1
      end
    end

    it 'should not find the user' do
      shell("mysql -NBe \"SHOW GRANTS FOR atest@tester\"") do |r|
        r.stderr.should =~ /There is no such grant defined for user 'atest' on host 'tester'/
        r.exit_code.should eq 1
      end
    end
  end

  describe 'adding privileges' do
    it 'should work without errors' do
      pp = <<-EOS
        mysql_grant { 'test2@tester/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test2@tester',
          privileges => ['SELECT', 'UPDATE'],
        }
      EOS

      puppet_apply(pp)
    end

    it 'should find the user' do
      shell("mysql -NBe \"SHOW GRANTS FOR test2@tester\"") do |r|
        r.stdout.should =~ /GRANT SELECT, UPDATE.*TO 'test2'@'tester'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
  end

  describe 'adding option' do
    it 'should work without errors' do
      pp = <<-EOS
        mysql_grant { 'test3@tester/test.*':
          ensure  => 'present',
          table   => 'test.*',
          user    => 'test3@tester',
          options => ['GRANT'],
          privileges => ['SELECT', 'UPDATE'],
        }
      EOS

      puppet_apply(pp)
    end

    it 'should find the user' do
      shell("mysql -NBe \"SHOW GRANTS FOR test3@tester\"") do |r|
        r.stdout.should =~ /GRANT SELECT, UPDATE ON `test`.* TO 'test3'@'tester' WITH GRANT OPTION$/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
  end

  describe 'adding all privileges without table' do
    it 'should fail' do
      pp = <<-EOS
        mysql_grant { 'test4@tester/test.*':
          ensure     => 'present',
          user       => 'test4@tester',
          options    => ['GRANT'],
          privileges => ['SELECT', 'UPDATE', 'ALL'],
        }
      EOS

      puppet_apply(pp) do |r|
        r.stderr.should =~ /table parameter is required./
      end
    end

  end


  describe 'adding all privileges' do
    it 'should only try to apply ALL' do
      pp = <<-EOS
        mysql_grant { 'test4@tester/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test4@tester',
          options    => ['GRANT'],
          privileges => ['SELECT', 'UPDATE', 'ALL'],
        }
      EOS

      puppet_apply(pp)
    end

    it 'should find the user' do
      shell("mysql -NBe \"SHOW GRANTS FOR test4@tester\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test4'@'tester' WITH GRANT OPTION/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
  end

  # Test combinations of user@host to ensure all cases work.
  describe 'short hostname' do
    it 'should apply' do
      pp = <<-EOS
        mysql_grant { 'test@short/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test@short',
          privileges => 'ALL',
        }
        mysql_grant { 'test@long.hostname.com/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test@long.hostname.com',
          privileges => 'ALL',
        }
        mysql_grant { 'test@192.168.5.6/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test@192.168.5.6',
          privileges => 'ALL',
        }
        mysql_grant { 'test@2607:f0d0:1002:0051:0000:0000:0000:0004/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test@2607:f0d0:1002:0051:0000:0000:0000:0004',
          privileges => 'ALL',
        }
        mysql_grant { 'test@::1/128/test.*':
          ensure     => 'present',
          table      => 'test.*',
          user       => 'test@::1/128',
          privileges => 'ALL',
        }
      EOS

      puppet_apply(pp)
    end

    it 'finds short hostname' do
      shell("mysql -NBe \"SHOW GRANTS FOR test@short\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test'@'short'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
    it 'finds long hostname' do
      shell("mysql -NBe \"SHOW GRANTS FOR 'test'@'long.hostname.com'\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test'@'long.hostname.com'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
    it 'finds ipv4' do
      shell("mysql -NBe \"SHOW GRANTS FOR 'test'@'192.168.5.6'\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test'@'192.168.5.6'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
    it 'finds ipv6' do
      shell("mysql -NBe \"SHOW GRANTS FOR 'test'@'2607:f0d0:1002:0051:0000:0000:0000:0004'\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test'@'2607:f0d0:1002:0051:0000:0000:0000:0004'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
    it 'finds short ipv6' do
      shell("mysql -NBe \"SHOW GRANTS FOR 'test'@'::1/128'\"") do |r|
        r.stdout.should =~ /GRANT ALL PRIVILEGES ON `test`.* TO 'test'@'::1\/128'/
        r.stderr.should be_empty
        r.exit_code.should be_zero
      end
    end
  end

  describe 'complex test' do
    it 'setup mysql::server' do
      pp = <<-EOS
      $dbSubnet = '10.10.10.%'

      mysql_database { 'foo':
        ensure => present,
      }

      exec { 'mysql-create-table':
        command     => '/usr/bin/mysql -NBe "CREATE TABLE foo.bar (name VARCHAR(20))"',
        environment => "HOME=${::root_home}",
        unless      => '/usr/bin/mysql -NBe "SELECT 1 FROM foo.bar LIMIT 1;"',
        require     => Mysql_database['foo'],
      }

      Mysql_grant {
          ensure     => present,
          options    => ['GRANT'],
          privileges => ['ALL'],
          table      => '*.*',
          require    => [ Mysql_database['foo'], Exec['mysql-create-table'] ],
      }

      mysql_grant { "user1@${dbSubnet}/*.*":
          user       => "user1@${dbSubnet}",
      }
      mysql_grant { "user2@${dbSubnet}/foo.bar":
          privileges => ['SELECT', 'INSERT', 'UPDATE'],
          user       => "user2@${dbSubnet}",
          table      => 'foo.bar',
      }
      mysql_grant { "user3@${dbSubnet}/foo.*":
          privileges => ['SELECT', 'INSERT', 'UPDATE'],
          user       => "user3@${dbSubnet}",
          table      => 'foo.*',
      }
      mysql_grant { 'web@%/*.*':
          user       => 'web@%',
      }
      mysql_grant { "web@${dbSubnet}/*.*":
          user       => "web@${dbSubnet}",
      }
      mysql_grant { "web@${fqdn}/*.*":
          user       => "web@${fqdn}",
      }
      mysql_grant { 'web@localhost/*.*':
          user       => 'web@localhost',
      }
      EOS

      puppet_apply(pp) do |r|
        r.exit_code.should_not == 1
        r.refresh
        r.exit_code.should be_zero
      end
    end
  end

  describe 'lower case privileges' do
    it 'create ALL privs' do
      pp = <<-EOS
      mysql_grant { 'lowercase@localhost/*.*':
          user       => 'lowercase@localhost',
          privileges => 'ALL',
          table      => '*.*',
      }
      EOS

      puppet_apply(pp)
    end

    it 'create lowercase all privs' do
      pp = <<-EOS
      mysql_grant { 'lowercase@localhost/*.*':
          user       => 'lowercase@localhost',
          privileges => 'all',
          table      => '*.*',
      }
      EOS

      puppet_apply(pp) do |r|
        r.exit_code.should be_zero
      end
    end
  end

end
