require 'spec_helper'

describe 'postgresql::params', :type => :class do
  let :facts do
    {
      :osfamily => 'Debian',
      :operatingsystem => 'Debian',
      :operatingsystemrelease => '6.0',
    }
  end
  it { should contain_class("postgresql::params") }
end
