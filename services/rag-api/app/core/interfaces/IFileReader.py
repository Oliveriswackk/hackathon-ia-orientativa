from abc import ABC, abstractmethod
from pathlib import Path


class IFileReader(ABC):

    @staticmethod
    def readPDF(self, path_pdf: Path) -> str: pass

    @staticmethod
    def readTextfile(self, path_textfile: Path) -> str: pass



