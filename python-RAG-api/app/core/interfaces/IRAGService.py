from abc import ABC, abstractmethod


class IRAGService(ABC):
    @abstractmethod
    def rag_prompt(self, prompt: str) -> str:
        pass

