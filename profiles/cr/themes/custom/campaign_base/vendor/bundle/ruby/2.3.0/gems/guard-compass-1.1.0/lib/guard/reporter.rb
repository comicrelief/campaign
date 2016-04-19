module Guard
  # Send a report to the Guard UI
  # The Reporter is a wrapper arround guard UI because
  # it is currently subject to change.
  class Reporter
    def success(message)
      UI.info(message)
    end
    def failure(message)
      UI.error(message)
    end
    def unstable(message)
      UI.info(message)
    end
    def announce(message)
      UI.info(message)
    end
  end
end