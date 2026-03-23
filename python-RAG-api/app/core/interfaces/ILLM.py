from abc import ABC, abstractmethod


# Initialize Groq LLM
class ILLM(ABC):

    @abstractmethod
    def send_prompt(self, prompt: str) -> str:
        pass


