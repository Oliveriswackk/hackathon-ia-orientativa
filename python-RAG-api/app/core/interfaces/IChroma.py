from abc import ABC, abstractmethod
from chromadb import Collection


class IChroma(ABC):
    @abstractmethod
    def add_Documents(self) -> None: pass

    @abstractmethod
    def queryCollection(self, query_text: str) -> dict: pass